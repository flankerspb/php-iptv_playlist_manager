$(document).ready(function(){
  
  var channels = $('table#channels > tbody > tr');
  
  //switch groups
  $('ul.nav.nav-tabs li').click(function (e) {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');
    
    var id = $(this).data('group');
    
    channels.removeClass('hidden');
    
    if(id != -1){
      channels.not('.group-' + id).addClass('hidden');
    }
    
    //channels.filter('.group-' + id).removeClass('hidden');
  });
  
  //sort groups (group tabs)
  $('ul.nav.nav-tabs').sortable({
    items: 'li.sortable'
  });
  
  //sort channel
  $('tbody.sortable').sortable({
    handle:'.handle',
    placeholder: '<tr class="placeholder"/>'
  });
});