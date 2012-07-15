$(function(){
  var $edit = $('.edit'),
      action = 'read',
      pathname = window.location.pathname,
      elementid = [];
  $edit.each(function(){    
    if ($edit.length > 1){
      var thisid = $(this).attr('id');
      if (thisid){
        elementid.push(thisid);
      }
      else {
        throw('You can not have multiple sedit regions without each having an id');
      }
    }
    else {
      if ($(this).attr('id')) elementid = $(this).attr('id');
    }
  });
  // Page Load Read content in
  xhr = $.ajax({
    type: 'POST',
    url: 'sedit/sedit.php',
    data: {
      action: action,
      pathname: pathname,
      elementid: elementid
    },
    success: function(result){
      var result= jQuery.parseJSON(result),
          regions = result.regions;
      for(var i=0,l=regions.length; i<l; i++){
        var region = regions[i];
        if (region.elementid) $('#' + region.elementid).html(region.content);
        else $edit.html(region.content);
      }
      if (result.loggedin) $edit.attr('contenteditable', 'true');
    },
    complete: function(){
      $edit.fadeTo('slow', 1);
    }
  });

  $edit.blur(function(){
    var $this = $(this);
    if (!$this.html()) return false; // Only proceed if their is content to write
    $.ajax({
      type: 'POST',
      url: 'sedit/sedit.php',
      data: {
        action: 'write',
        pathname: window.location.pathname, 
        content: $this.html(),
        elementid: $this.attr('id')
      },
      success: function(result){
        var result= jQuery.parseJSON(result);
      }
    });
  });
        
});