
    function GenerarPdf(id,documento){
   
        //alert(id);
        $("#emPdf").attr('src','');
        $.ajax({
            type: "GET",
            url: "/ride/"+id+"/"+documento,
            cache: false,
            success: function(data){
               // console.log(data);
                 $("#emPdf").attr('src','data:application/pdf;base64,'+data);
               $("#modalVerFactura").modal();
            }
          });
}

function enviarRideCliente(id,documento){
    //alert(id);
    $.ajax({
        type: "GET",
        url: "/mail/"+id+"/"+documento,
        cache: false,
        success: function(data){
            //console.log(data);
            if(data){
                swal("Correo enviado correctamente.");
            }else{
                swal({
                    title: "Error?",
                    text: "Error al enviar email!",
                    icon: "warning",
                    button: true,
                    dangerMode: true,
                  })
                  .then((willDelete) => {
                    if (willDelete) {
                        swal.close();
                        return;
                    }
                  });
            }
        }
      });
}

function DescargarXml(id,documento){
    
    window.open('xml/'+id+'/'+documento, '_blank');
    
}