$(document).ready(function() {
    $("#formatocaja").bind("submit",function(){
      // Capturamnos el boton de envío
       //alert('enviadatos');
       
      var btncancelacion = $("#btncancelacion");

      event.preventDefault(); 
                  
        $.ajax({
            type: $(this).attr("method"),
            url: $(this).attr("action"),
            data:$(this).serialize(),
            beforeSend: function(){
                /*
                * Esta función se ejecuta durante el envió de la petición al
                * servidor.
                * */
                // btnEnviar.text("Enviando"); Para button 
                btncancelacion.val("Enviando"); // Para input de tipo button
                btncancelacion.attr("disabled","disabled");
            },
            complete:function(data){
                /*
                * Se ejecuta al termino de la petición
                * */
                btncancelacion.val("Guardado");
              
            },
            success: function(data){
                /*
                * Se ejecuta cuando termina la petición y esta ha sido
                * correcta
                * */
               // $("#mensajeniveles").html(data);
  
                Command: toastr["success"](data);
  
                toastr.options = {
                  "closeButton": false,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": false,
                  "positionClass": "toast-top-right",
                  "preventDuplicates": true,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                }
                location.reload(true);
  
                
            },
            error: function(data){
                /*
                * Se ejecuta si la peticón ha sido erronea
                * */
                alert("Problemas al tratar de enviar el formulario");
            }
        });
        // Nos permite cancelar el envio del formulario
        return false;
    });
    $("#atencioncaja").bind("submit",function(){
        // Capturamnos el boton de envío
         //alert('enviadatos');
         
        var btnatendido = $("#btnatendido");
  
        event.preventDefault(); 
                    
          $.ajax({
              type: $(this).attr("method"),
              url: $(this).attr("action"),
              data:$(this).serialize(),
              beforeSend: function(){
                  /*
                  * Esta función se ejecuta durante el envió de la petición al
                  * servidor.
                  * */
                  // btnEnviar.text("Enviando"); Para button 
                  btnatendido.val("Enviando"); // Para input de tipo button
                  btnatendido.attr("disabled","disabled");
              },
              complete:function(data){
                  /*
                  * Se ejecuta al termino de la petición
                  * */
                  btnatendido.val("Guardado");
                
              },
              success: function(data){
                  /*
                  * Se ejecuta cuando termina la petición y esta ha sido
                  * correcta
                  * */
                 // $("#mensajeniveles").html(data);
    
                  Command: toastr["success"](data);
    
                  toastr.options = {
                    "closeButton": false,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": false,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": true,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                  }
                  location.reload(true);
    
                  
              },
              error: function(data){
                  /*
                  * Se ejecuta si la peticón ha sido erronea
                  * */
                  alert("Problemas al tratar de enviar el formulario");
              }
          });
          // Nos permite cancelar el envio del formulario
          return false;
    });
        //cancelar nivel
    $("#cancelarcaja").click(function(){
        $("#cancelacion").css("display", "none");
      });
              //cancelar nivel
    $("#cancelar").click(function(){
        $("#atendido").css("display", "none");
      });
  });
  $(document).ready(function() {
    // Setup - add a text input to each footer cell

        $('#example thead tr').clone(true).appendTo( '#example thead' );
        $('#example thead tr:eq(1) th').each( function (i) {
            var title = $(this).text();
            $(this).html( '<input type="text" placeholder="Buscar '+title+'" />' );
    
            $( 'input', this ).on( 'keyup change', function () {
                if ( table.column(i).search() !== this.value ) {
                    table
                        .column(i)
                        .search( this.value )
                        .draw();
                }
            } );
        });
        
    

 
var table = $('#example').DataTable( {
    orderCellsTop: true,
    fixedHeader: true,
    "responsive": true,
    "language":{
    "lengthMenu": "Mostrando _MENU_ registros por página",
        "zeroRecords": "Nada que mostrar",
        "info": "Mostrando página _PAGE_ de _PAGES_",
        "infoFiltered": "(Coincidencias encontradas de _MAX_ registros)",
        search: '', searchPlaceholder: "Buscar..."
        }
    } );
} );
  
  
  