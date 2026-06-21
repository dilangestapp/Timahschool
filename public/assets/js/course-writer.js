(function(){
  function sync(editor){var target=document.querySelector(editor.dataset.target);if(target){target.value=editor.innerHTML;}}
  function command(editor,cmd,value){editor.focus();document.execCommand(cmd,false,value||null);sync(editor);}
  function tableHtml(){var html='<table><tbody>';for(var r=0;r<3;r++){html+='<tr>';for(var c=0;c<3;c++){html+='<td>&nbsp;</td>';}html+='</tr>';}return html+'</tbody></table><p><br></p>';}
  function wrapSelection(editor,style){editor.focus();var sel=window.getSelection();if(!sel||sel.rangeCount===0){return;}var range=sel.getRangeAt(0);var span=document.createElement('span');span.setAttribute('style',style);try{range.surroundContents(span);}catch(e){span.appendChild(range.extractContents());range.insertNode(span);}sync(editor);}
  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-cw-cmd]');if(!btn)return;e.preventDefault();var shell=btn.closest('.course-word-shell');if(!shell)return;var editor=shell.querySelector('.course-word-page');if(!editor)return;var cmd=btn.dataset.cwCmd;
    if(cmd==='table'){command(editor,'insertHTML',tableHtml());return;}
    if(cmd==='pagebreak'){command(editor,'insertHTML','<hr><p><br></p>');return;}
    if(cmd==='fullscreen'){shell.classList.toggle('course-word-fullscreen');return;}
    if(cmd==='link'){var link=prompt('Adresse du lien');if(link){command(editor,'createLink',link);}return;}
    command(editor,cmd,btn.dataset.cwValue||null);
  });
  document.addEventListener('change',function(e){
    var ctl=e.target.closest('[data-cw-change]');if(!ctl)return;var shell=ctl.closest('.course-word-shell');var editor=shell?shell.querySelector('.course-word-page'):null;if(!editor)return;var type=ctl.dataset.cwChange;
    if(type==='format'){command(editor,'formatBlock',ctl.value);return;}
    if(type==='fontName'){command(editor,'fontName',ctl.value);return;}
    if(type==='fontSize'){command(editor,'fontSize',ctl.value);return;}
    if(type==='foreColor'){command(editor,'foreColor',ctl.value);return;}
    if(type==='backColor'){command(editor,'backColor',ctl.value);return;}
    if(type==='lineHeight'){wrapSelection(editor,'line-height:'+ctl.value);return;}
  });
  document.addEventListener('input',function(e){var editor=e.target.closest('.course-word-page');if(editor)sync(editor);});
  document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.course-word-page').forEach(function(editor){sync(editor);});document.querySelectorAll('form').forEach(function(form){form.addEventListener('submit',function(){form.querySelectorAll('.course-word-page').forEach(function(editor){sync(editor);});});});});
})();
