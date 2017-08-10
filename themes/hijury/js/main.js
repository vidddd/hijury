
$(function() {
 $( "#ptabs" ).tabs();
 var counta = 1;
 var videos = [];
 
 $('.videocontainer div.videoitem').each(function(){
     videos.push(counta);
     counta++;
 });
 
 $('#videog1').show(); $(".actual").html("1");
 $("#siguiente").attr('rel', "2");
 $("#anterior").attr('rel', videos.length);
  
 $('#siguiente').click(function() { // bind click event to link
     var siguiente = $(this).attr('rel');    
     var anterior = siguiente - 1;
     for(i=1;i<=videos.length;i++){
         $('#videog'+i).hide();
     }
     
     $('#videog'+siguiente).show();$('.actual').html(siguiente);
     
     if (parseInt(siguiente) === videos.length) { siguiente = 1; anterior = videos.length - 1; } else { siguiente = parseInt(siguiente) + 1; }
     

     $("#siguiente").attr('rel', siguiente);
     $("#anterior").attr('rel', anterior);

    });
    
 $('#anterior').click(function() { // bind click event to link
    var anterior = $(this).attr('rel'); 
    var siguiente = 1;
    
    for(i=1;i<=videos.length;i++){
         $('#videog'+i).hide();
     }
    $('#videog'+anterior).show();$('.actual').html(anterior);
    
     if (parseInt(anterior) === 1) { anterior = videos.length; siguiente = parseInt(anterior) + 1; } else { anterior = parseInt(anterior) - 1; }
     
     $("#siguiente").attr('rel', siguiente);
     $("#anterior").attr('rel', anterior);
 });
 
});		

