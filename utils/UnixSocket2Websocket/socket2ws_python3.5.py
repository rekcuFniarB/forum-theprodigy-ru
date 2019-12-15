#!/usr/bin/env python3.5

## Simoke websockets push server. It receives messages to the UNIX socket
## and sends to all clients connected to websocket.
## Expected message format at the UNIX socket is "path\0message".
## This version if for python3.5.

import websockets
import asyncio
import ssl
import os
import sys
import json

conf = {
    "host": "0.0.0.0",
    "port": 8443,
    "SSLCert": "/tmp/cert.pem",
    "SSLPrivKey": "/tmp/privkey.pem",
    "UNIXSocket": "/tmp/websocket.sock",
    "SSL": True,
    "debug": False
}

connections = {}
USocketServer = None

def stderr(msg):
    if(conf['debug']):
        sys.stderr.write('%s\n' % msg)

def logmsg(*msg):
    if(conf['debug']):
        print(*msg)

async def sockHandler(reader, writer):
    '''Handle incoming connection to the UNIX socket, get incoming messae
    and retransmit to websocket clients.'''
    
    msg = await reader.read() ## Generator object? wtf???
    path, msg = msg.decode('utf-8').split('\0') ## expecting ['path', 'message']
    if path in connections.keys():
        results = []
        
        for ws in connections[path]:
            if ws is None:
                continue
            try:
                result = ws.send(msg)
                results.append(result)
            except (websockets.exceptions.ConnectionClosed):
                await ws.close()
                ## Forget this connection
                erase_connection(path, ws)
            
        await asyncio.gather(*results)

async def broadcast(path):
    '''Example messages broadcast function just for testing.'''
    while True:
        if path in connections.keys():
            for connection in path:
                for x in range(0, 10):
                    result = await client.send('Hello %s' % x)
                    logmsg('Sent', result, client)
                    await asyncio.sleep(2)
        else:
           logmsg('No path', connections)
           await asyncio.sleep(3)

## store connection in the pool
def store_connection(path, ws):
    if not path in connections.keys():
        ## Create connections pool for specified path if not exists.
        connections[path] = []
    
    if None in connections[path]:
        index = connections[path].index(None)
        connections[path][index] = ws
    else:
        connections[path].append(ws)
        index = connections[path].index(ws)
    return index

def erase_connection(path, ws):
    index = connections[path].index(ws)
    connections[path][index] = None
    return index

async def wshandler(ws, path):
    '''Websocket incoming connections handler.'''
    
    ## Store connection
    store_connection(path, ws)
    
    ## get ID of object
    wsid = id(ws)
    logmsg('\nNew connection %s' % wsid)
    logmsg(connections)
    wsalive = True
    while wsalive:
        try:
            ## Without this we loose connection immediately
            incoming = await ws.recv()
            logmsg(incoming)
        except (websockets.exceptions.ConnectionClosed):
            logmsg('Connecton closed by', wsid, ws)
            await ws.close()
            ## Exit loop if connection lost
            wsalive = False
            ## forget connection
            #del(connections[path][index]) ## deleting probably is bad idea in async code
            ## instead just erasing
            erase_connection(path, ws)

async def closews():
    for path in connections.items():
        for connection in path:
            logmsg("Closing ", connection)
            await connection.close()

def prepareexit():
    USocketServer.close()
    #loop.close() # closing manually raises to much exceptions
    os.remove(conf['UNIXSocket'])

def main():
    global USocketServer, conf
    
    with open(sys.argv[1], 'r') as f:
        conf = json.load(f)
    
    tasks = []
    ## Start UNIX socket listening
    USocketServer = asyncio.start_unix_server(sockHandler, path=conf['UNIXSocket'])
    
    ssl_context = None
    if(conf['SSL']):
        ssl_context = ssl.SSLContext(ssl.PROTOCOL_TLS)
        ssl_context.load_cert_chain(conf['SSLCert'], conf['SSLPrivKey'])
    
    wsserver = websockets.serve(wshandler, conf['host'], conf['port'], ssl=ssl_context)
    
    #tasks.append(asyncio.ensure_future(closews()))
    #tasks.append(asyncio.ensure_future(broadcast('/qwerty/')))
    tasks.append(asyncio.ensure_future(wsserver))
    tasks.append(asyncio.ensure_future(USocketServer))
    return tasks

if __name__ == '__main__':
    try:
        loop = asyncio.get_event_loop()
        loop.run_until_complete(asyncio.wait(main()))
        loop.run_forever()
    except KeyboardInterrupt:
        sys.stderr.write(' Interrupted by user.\n')
    finally:
        prepareexit()
