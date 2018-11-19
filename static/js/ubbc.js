function DoConfirm(message, url) {
   if(confirm(message)) location.href = url;
}

function WhichClicked(ww) {
   window.document.postmodify.waction.value = ww;
}

function submitonce(theform) {
   // if IE 4+ or NS 6+
   if (document.all || document.getElementById) {
      // hunt down "submit" and "reset"
      for (i=0;i<theform.length;i++) {
         var tempobj=theform.elements[i];
         if(tempobj.type.toLowerCase()=="submit"||tempobj.type.toLowerCase()=="reset") {
            //disable it
            tempobj.disabled=true;
         }
      }
   }
}

// Remember the current position.
function storeCaret(text)
{
   // Only bother if it will be useful.
   if (typeof(text.createTextRange) != "undefined")
      text.caretPos = document.selection.createRange().duplicate();
}

// Replaces the currently selected text with the passed text.
function replaceText(text, field)
{
    field = typeof field !== 'undefined' ? field : document.forms.postmodify.message;
   // Attempt to create a text range (IE).
   if (typeof(field.caretPos) != "undefined" && field.createTextRange)
   {
      var caretPos = field.caretPos;

      caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
      caretPos.select();
   }
   // Mozilla text range replace.
   else if (typeof(field.selectionStart) != "undefined")
   {
      var begin = field.value.substr(0, field.selectionStart);
      var end = field.value.substr(field.selectionEnd);
      var scrollPos = field.scrollTop;

      field.value = begin + text + end;

      if (field.setSelectionRange)
      {
         field.focus();
         field.setSelectionRange(begin.length + text.length, begin.length + text.length);
      }
      field.scrollTop = scrollPos;
   }
   // Just put it on the end.
   else
   {
      field.value += text;
      field.focus(field.value.length - 1);
   }
}


// Surrounds the selected text with text1 and text2.
function surroundText(text1, text2)

{
   // Can a text range be created?
   if (typeof(document.forms.postmodify.message.caretPos) != "undefined" && document.forms.postmodify.message.createTextRange)
   {
      var caretPos = document.forms.postmodify.message.caretPos, temp_length = caretPos.text.length;

      caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text1 + caretPos.text + text2 + ' ' : text1 + caretPos.text + text2;

      if (temp_length == 0)
      {
         caretPos.moveStart("character", -text2.length);
         caretPos.moveEnd("character", -text2.length);
         caretPos.select();
      }
      else
         document.forms.postmodify.message.focus(caretPos);
   }
   // Mozilla text range wrap.
   else if (typeof(document.forms.postmodify.message.selectionStart) != "undefined")
   {
      var begin = document.forms.postmodify.message.value.substr(0, document.forms.postmodify.message.selectionStart);
      var selection = document.forms.postmodify.message.value.substr(document.forms.postmodify.message.selectionStart, document.forms.postmodify.message.selectionEnd - document.forms.postmodify.message.selectionStart);
      var end = document.forms.postmodify.message.value.substr(document.forms.postmodify.message.selectionEnd);
      var newCursorPos = document.forms.postmodify.message.selectionStart;
      var scrollPos = document.forms.postmodify.message.scrollTop;

      document.forms.postmodify.message.value = begin + text1 + selection + text2 + end;

      if (document.forms.postmodify.message.setSelectionRange)
      {
         if (selection.length == 0)
            document.forms.postmodify.message.setSelectionRange(newCursorPos + text1.length, newCursorPos + text1.length);
         else
            document.forms.postmodify.message.setSelectionRange(newCursorPos, newCursorPos + text1.length + selection.length + text2.length);
         document.forms.postmodify.message.focus();
      }
      document.forms.postmodify.message.scrollTop = scrollPos;
   }
   // Just put them on the end, then.
   else
   {
      document.forms.postmodify.message.value += text1 + text2;
      document.forms.postmodify.message.focus(document.forms.postmodify.message.value.length - 1);
   }
}


function isEmptyText(theField)
{
   while (theField.value.length > 0 && (theField.value.charAt(0)==' ' || theField.value.charAt(0)=='\t'))
      theField.value = theField.value.substring(1,theField.value.length);
   while (theField.value.length > 0 && (theField.value.charAt(theField.value.length-1)==' ' || theField.value.charAt(theField.value.length-1)=='\t'))
      theField.value = theField.value.substring(0,theField.value.length-1);

   if (theField.value=='')
      return true;
   else
      return false;
}
