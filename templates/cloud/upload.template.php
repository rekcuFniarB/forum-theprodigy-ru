<div class="bordercolor">
  <div class="windowbg2 cloud">
    <h1 class="titlebg">Prodigy Cloud</h1>
    <form action="." method="POST" enctype="multipart/form-data">
      <input type="text" name="title" maxlength="50" placeholder="Title" required ><br>
      <textarea cols="100" rows="5" name="description" placeholder="Description" maxlength="256"></textarea><br>
      <input type="hidden" name="MAX_FILE_SIZE" value="60000000">
      <input type="file" name="uplfile" required>
      <input type="hidden" name="sc" value="<?= $this->sessid ?>">
      <input type="submit" value="Upload">
    </form>
    
    <div class="status">
      <div class="uploading">Uploading...</div>
      <div class="preparing">Preparing...</div>
      <div class="error">Error occurred.</div>
    </div>
  </div>
</div>

<script>
  if (typeof(FormData) === 'function')
  {
      $(document).on('ready', function(){
          var form = $('.cloud form');
          form.on('submit', function(event){
              event.preventDefault();
              form.hide();
              $('.status > .uploading').show();
              var data = new FormData(form[0]);
              $.ajax({
                  url: '.',
                  type: 'POST',
                  data: data,
                  processData: false,
                  contentType: false,
                  error: function() {
                      $('.status > .error').show();
                  },
                  success: function(response){
                      if (response[0] == '__ERROR__')
                          $('.status > .error').text('Upload failed.').show();
                      else
                          document.location.pathname += 'show/' + response[0] + '/';
                  }
              }); // ajax
          });
      });
  }
</script>
