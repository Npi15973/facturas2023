function obtenerComprobanteFirmado_sri(id,documento) {
    $.ajax({
        type: "GET",
        url: "/validar",
        cache: false,
        success: function(data){
            console.log(data);
            if(!data){
                swal({
                        title:"Mensaje",
                        text: "Configure el Emisor antes de emitir documentos electrónicos!",
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

                    return;
            }else{
                $("#modalLoading").modal(
                    {backdrop: 'static',
                    keyboard: false}
                );
                var pwd_p12 ="";
                $.ajax({
                    type: "GET",
                    url: "/pass",
                    cache: false,
                    success: function(data){
                        //console.log(data);
                        pwd_p12= data;
                    }
                  });
                var ruta_certificado="/certificado";
                var ruta_respuesta="";
                var response = [];
                var  parametros={
                    '_token':$('input[name=_token]').val(),
                    'id':id,
                    'documento':documento,
                  }
                $.ajax({
                    url: "/leerDocumento",
                    type: 'POST',
                    data: parametros,
                }).done(function (respuesta) {    
                    window.contenido_comprobante = respuesta;
                    var oReq = new XMLHttpRequest();
                    oReq.open("GET", ruta_certificado, true);
                    oReq.responseType = "arraybuffer";
                    oReq.onload = function (oEvent) {
                        var blob = new Blob([oReq.response], {type: "application/x-pkcs12"});
                        window.contenido_p12 = [oReq.response];
                        var comprobanteFirmado_xml = firmarComprobante(window.contenido_p12[0],pwd_p12,window.contenido_comprobante);
                        $.ajax({
                            url: "/guardarXmlFirmado",
                            type: 'POST',
                            data: {
                                'xmlFirmado': comprobanteFirmado_xml,
                                'id':id,
                                'documento':documento
                            },
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            context: document.body
                        }).done(function (respuesta) {
                            service = 'Validar Comprobante';
                            xmlDoc = $.parseXML(window.contenido_comprobante),
                            $xml = $(xmlDoc),
                            $claveAcceso = $xml.find("claveAcceso");
                            $.ajax({
                                type: 'POST',
                                url: "/enviarDocumento",
                                data: {'documento':documento,'claveAcceso': $claveAcceso.text()},
                                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                success: function(result){
                                        //console.log(result);
                                        $("#modalLoading").modal('hide');
                                        swal({
                                            title: "Respuesta SRI!",
                                            text: result,
                                            icon: "success",
                                            button: true,
                                            dangerMode: true,
                                          })
                                          .then((willDelete) => {
                                            if (willDelete) {
                                                swal.close();
                                                window.location.reload();
                                                return;
                                            }
                                          });
                                        return;
                              }});
                        });
                    }
                    oReq.send();
                }
                );                
            }
        }
      });
}

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
function fechas_certificado(ruta_certificado, mi_pwd_p12, ruta_respuesta) {

    var response = [];

    var oReq = new XMLHttpRequest();
    oReq.open("GET", ruta_certificado, true);
    oReq.responseType = "arraybuffer";
    oReq.onload = function (oEvent) {
        var blob = new Blob([oReq.response], {type: "application/x-pkcs12"});
        window.contenido_p12 = [oReq.response];
        var arrayUint8 = new Uint8Array(window.contenido_p12[0]);
        var p12B64 = forge.util.binary.base64.encode(arrayUint8);
        var p12Der = forge.util.decode64(p12B64);
        var p12Asn1 = forge.asn1.fromDer(p12Der);
        var p12 = forge.pkcs12.pkcs12FromAsn1(p12Asn1, mi_pwd_p12);
        var certBags = p12.getBags({bagType: forge.pki.oids.certBag})
        var cert = certBags[forge.oids.certBag][0].cert;
        //Validar Fecha de vencimiento del p12
        var fechaInicio = cert.validity['notBefore'];
        var fechaFin = cert.validity['notAfter'];
        var response = [];
        response[0] = fechaInicio;
        response[1] = fechaFin;

        $.ajax({
            type: 'POST',
            url: "src/validarFechaCertificado.php",
            data: {
                'fechaInicio': fechaInicio,
                'fechaFin': fechaFin
            },
            context: document.body
        }).done(function (respuesta) {
            response[2] = respuesta;
            $.ajax({
                type: 'POST',
                url: ruta_respuesta,
                data: {'respuestaValidarVigencia': response},
                context: document.body
            }).done(function (respuesta) {
                return true;
            });
        });
    }
    oReq.send();
}



function validar_pwrd(ruta_certificado, mi_pwd_p12, ruta_respuesta) {

    var oReq = new XMLHttpRequest();
    oReq.open("GET", ruta_certificado, true);
    oReq.responseType = "arraybuffer";
    oReq.onload = function (oEvent) {
        var blob = new Blob([oReq.response], {type: "application/x-pkcs12"});
        window.contenido_p12 = [oReq.response];

        var arrayUint8 = new Uint8Array(window.contenido_p12[0]);

        var p12B64 = forge.util.binary.base64.encode(arrayUint8);

        var p12Der = forge.util.decode64(p12B64);

        var p12Asn1 = forge.asn1.fromDer(p12Der);

        try {

            forge.pkcs12.pkcs12FromAsn1(p12Asn1, mi_pwd_p12);
            $.ajax({
                type: 'POST',
                url: ruta_respuesta,
                data: {'respuestaValidarContraseña': 'Contraseña Correcta'},
                context: document.body
            }).done(function (respuesta) {
                return "contraseña valida"
            });

        } catch (err) {
            $.ajax({
                type: 'POST',
                url: ruta_respuesta,
                data: {'respuestaValidarContraseña': 'Contraseña Invalida'},
                context: document.body
            }).done(function (respuesta) {
                return "contraseña invalida"
            });
        }
    }
    oReq.send();
}





var contenido_p12 = null;

/**
 * firma documento
 */
function firmarComprobante(mi_contenido_p12, mi_pwd_p12, comprobante) {



    var arrayUint8 = new Uint8Array(mi_contenido_p12);
    console.log(arrayUint8)
    var p12B64 = forge.util.binary.base64.encode(arrayUint8);
    var p12Der = forge.util.decode64(p12B64);
    var p12Asn1 = forge.asn1.fromDer(p12Der);

    var p12 = forge.pkcs12.pkcs12FromAsn1(p12Asn1, mi_pwd_p12);

    var certBags = p12.getBags({bagType: forge.pki.oids.certBag})
    var signaturesQuantity = certBags[forge.oids.certBag];
    var count = 0;
    var positionSignature = 0;
    var entidad = signaturesQuantity[0].attributes.friendlyName[0];
    if (/BANCO CENTRAL/i.test(entidad)) {
        entidad = 'BANCO_CENTRAL';
        var certBags = p12.getBags({bagType: forge.pki.oids.certBag})

        var cert = certBags[forge.oids.certBag][1].cert;
        // issuerName
        var issuerName = 'CN=AC BANCO CENTRAL DEL ECUADOR,L=QUITO,OU=ENTIDAD DE CERTIFICACION DE INFORMACION-ECIBCE,O=BANCO CENTRAL DEL ECUADOR,C=EC';
    } else if (/SECURITY DATA/i.test(entidad)) {
        entidad = 'SECURITY_DATA';
        var contador = 0;
        var max = 0;
        var attributes_array=[];
        certBags[forge.oids.certBag].forEach(function (entry) {
            var bag = entry.cert;
            var attributes = bag.extensions;

            attributes_array[contador] = attributes;
            attributes_array.sort().reverse();
            max = attributes_array[0].length;

            contador++;
            /*if (attributes.length >= 23) {
                cert = bag;
            }*/
        });

        certBags[forge.oids.certBag].forEach(function (entry) {
            var bag = entry.cert;
            var attributes = bag.extensions;
            if (attributes.length >= max) {
                cert = bag;
            }
        });



        // issuerName
        var issuerName = 'CN=AUTORIDAD DE CERTIFICACION SUB SECURITY DATA,OU=ENTIDAD DE CERTIFICACION DE INFORMACION,O=SECURITY DATA S.A.,C=EC';
    }
    else {
        entidad = "Tipo de certificado no registrado";
        return;
    }

    //Validar Fecha de vencimiento del p12
    var fechaInicio = cert.validity['notBefore'];
    var fechaFin = cert.validity['notAfter'];


    $.ajax({
        type: 'POST',
        url: "/validarFechaCertificado",
        data: {
            'fechaInicio': fechaInicio,
            'fechaFin': fechaFin
        },
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        context: document.body
    }).done(function (respuesta) {

      console.log(respuesta);
    });

    var pkcs8bags = p12.getBags({bagType: forge.pki.oids.pkcs8ShroudedKeyBag});

    if (entidad == 'BANCO_CENTRAL') {
        var pkcs8 = pkcs8bags[forge.oids.pkcs8ShroudedKeyBag][1];
    } else {
        var pkcs8 = pkcs8bags[forge.oids.pkcs8ShroudedKeyBag][0];
    }

    var key = pkcs8.key;

    if (key == null) {
        key = pkcs8.asn1;
    }

    certificateX509_pem = forge.pki.certificateToPem(cert);

    certificateX509 = certificateX509_pem;
    certificateX509 = certificateX509.substr(certificateX509.indexOf('\n'));
    certificateX509 = certificateX509.substr(0, certificateX509.indexOf('\n-----END CERTIFICATE-----'));

    certificateX509 = certificateX509.replace(/\r?\n|\r/g, '').replace(/([^\0]{76})/g, '$1\n');

    //Pasar certificado a formato DER y sacar su hash:
    certificateX509_asn1 = forge.pki.certificateToAsn1(cert);
    certificateX509_der = forge.asn1.toDer(certificateX509_asn1).getBytes();
    certificateX509_der_hash = sha1_base64(certificateX509_der);



    //Serial Number
    var X509SerialNumber = parseInt(cert.serialNumber, 16);

    exponent = hexToBase64(key.e.data[0].toString(16));
    modulus = bigint2base64(key.n);

    comprobante = comprobante.replace(/\t|\r/g, "")



    var sha1_comprobante = sha1_base64(comprobante.replace('<?xml version="1.0" encoding="UTF-8"?>', ''));


    var xmlns = 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:etsi="http://uri.etsi.org/01903/v1.3.2#"';


    //numeros involucrados en los hash:

    //var Certificate_number = 1217155;//p_obtener_aleatorio(); //1562780 en el ejemplo del SRI
    var Certificate_number = p_obtener_aleatorio(); //1562780 en el ejemplo del SRI

    //var Signature_number = 1021879;//p_obtener_aleatorio(); //620397 en el ejemplo del SRI
    var Signature_number = p_obtener_aleatorio(); //620397 en el ejemplo del SRI

    //var SignedProperties_number = 1006287;//p_obtener_aleatorio(); //24123 en el ejemplo del SRI
    var SignedProperties_number = p_obtener_aleatorio(); //24123 en el ejemplo del SRI

    //numeros fuera de los hash:

    //var SignedInfo_number = 696603;//p_obtener_aleatorio(); //814463 en el ejemplo del SRI
    var SignedInfo_number = p_obtener_aleatorio(); //814463 en el ejemplo del SRI

    //var SignedPropertiesID_number = 77625;//p_obtener_aleatorio(); //157683 en el ejemplo del SRI
    var SignedPropertiesID_number = p_obtener_aleatorio(); //157683 en el ejemplo del SRI

    //var Reference_ID_number = 235824;//p_obtener_aleatorio(); //363558 en el ejemplo del SRI
    var Reference_ID_number = p_obtener_aleatorio(); //363558 en el ejemplo del SRI

    //var SignatureValue_number = 844709;//p_obtener_aleatorio(); //398963 en el ejemplo del SRI
    var SignatureValue_number = p_obtener_aleatorio(); //398963 en el ejemplo del SRI

    //var Object_number = 621794;//p_obtener_aleatorio(); //231987 en el ejemplo del SRI
    var Object_number = p_obtener_aleatorio(); //231987 en el ejemplo del SRI







    var SignedProperties = '';

    SignedProperties += '<etsi:SignedProperties Id="Signature' + Signature_number + '-SignedProperties' + SignedProperties_number + '">';  //SignedProperties
    SignedProperties += '<etsi:SignedSignatureProperties>';
    SignedProperties += '<etsi:SigningTime>';

    //SignedProperties += '2016-12-24T13:46:43-05:00';//moment().format('YYYY-MM-DD\THH:mm:ssZ');
    SignedProperties += moment().format('YYYY-MM-DD\THH:mm:ssZ');

    SignedProperties += '</etsi:SigningTime>';
    SignedProperties += '<etsi:SigningCertificate>';
    SignedProperties += '<etsi:Cert>';
    SignedProperties += '<etsi:CertDigest>';
    SignedProperties += '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">';
    SignedProperties += '</ds:DigestMethod>';
    SignedProperties += '<ds:DigestValue>';

    SignedProperties += certificateX509_der_hash;

    SignedProperties += '</ds:DigestValue>';
    SignedProperties += '</etsi:CertDigest>';
    SignedProperties += '<etsi:IssuerSerial>';
    SignedProperties += '<ds:X509IssuerName>';
    SignedProperties += issuerName;
    SignedProperties += '</ds:X509IssuerName>';
    SignedProperties += '<ds:X509SerialNumber>';

    SignedProperties += X509SerialNumber;

    SignedProperties += '</ds:X509SerialNumber>';
    SignedProperties += '</etsi:IssuerSerial>';
    SignedProperties += '</etsi:Cert>';
    SignedProperties += '</etsi:SigningCertificate>';
    SignedProperties += '</etsi:SignedSignatureProperties>';
    SignedProperties += '<etsi:SignedDataObjectProperties>';
    SignedProperties += '<etsi:DataObjectFormat ObjectReference="#Reference-ID-' + Reference_ID_number + '">';
    SignedProperties += '<etsi:Description>';

    SignedProperties += 'contenido comprobante';

    SignedProperties += '</etsi:Description>';
    SignedProperties += '<etsi:MimeType>';
    SignedProperties += 'text/xml';
    SignedProperties += '</etsi:MimeType>';
    SignedProperties += '</etsi:DataObjectFormat>';
    SignedProperties += '</etsi:SignedDataObjectProperties>';
    SignedProperties += '</etsi:SignedProperties>'; //fin SignedProperties

    SignedProperties_para_hash = SignedProperties.replace('<etsi:SignedProperties', '<etsi:SignedProperties ' + xmlns);

    var sha1_SignedProperties = sha1_base64(SignedProperties_para_hash);


    var KeyInfo = '';

    KeyInfo += '<ds:KeyInfo Id="Certificate' + Certificate_number + '">';
    KeyInfo += '\n<ds:X509Data>';
    KeyInfo += '\n<ds:X509Certificate>\n';

    //CERTIFICADO X509 CODIFICADO EN Base64
    KeyInfo += certificateX509;

    KeyInfo += '\n</ds:X509Certificate>';
    KeyInfo += '\n</ds:X509Data>';
    KeyInfo += '\n<ds:KeyValue>';
    KeyInfo += '\n<ds:RSAKeyValue>';
    KeyInfo += '\n<ds:Modulus>\n';

    //MODULO DEL CERTIFICADO X509
    KeyInfo += modulus;

    KeyInfo += '\n</ds:Modulus>';
    KeyInfo += '\n<ds:Exponent>';

    //KeyInfo += 'AQAB';
    KeyInfo += exponent;

    KeyInfo += '</ds:Exponent>';
    KeyInfo += '\n</ds:RSAKeyValue>';
    KeyInfo += '\n</ds:KeyValue>';
    KeyInfo += '\n</ds:KeyInfo>';

    KeyInfo_para_hash = KeyInfo.replace('<ds:KeyInfo', '<ds:KeyInfo ' + xmlns);

    var sha1_certificado = sha1_base64(KeyInfo_para_hash);


    var SignedInfo = '';

    SignedInfo += '<ds:SignedInfo Id="Signature-SignedInfo' + SignedInfo_number + '">';
    SignedInfo += '\n<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315">';
    SignedInfo += '</ds:CanonicalizationMethod>';
    SignedInfo += '\n<ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1">';
    SignedInfo += '</ds:SignatureMethod>';
    SignedInfo += '\n<ds:Reference Id="SignedPropertiesID' + SignedPropertiesID_number + '" Type="http://uri.etsi.org/01903#SignedProperties" URI="#Signature' + Signature_number + '-SignedProperties' + SignedProperties_number + '">';
    SignedInfo += '\n<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">';
    SignedInfo += '</ds:DigestMethod>';
    SignedInfo += '\n<ds:DigestValue>';

    //HASH O DIGEST DEL ELEMENTO <etsi:SignedProperties>';
    SignedInfo += sha1_SignedProperties;

    SignedInfo += '</ds:DigestValue>';
    SignedInfo += '\n</ds:Reference>';
    SignedInfo += '\n<ds:Reference URI="#Certificate' + Certificate_number + '">';
    SignedInfo += '\n<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">';
    SignedInfo += '</ds:DigestMethod>';
    SignedInfo += '\n<ds:DigestValue>';

    //HASH O DIGEST DEL CERTIFICADO X509
    SignedInfo += sha1_certificado;

    SignedInfo += '</ds:DigestValue>';
    SignedInfo += '\n</ds:Reference>';
    SignedInfo += '\n<ds:Reference Id="Reference-ID-' + Reference_ID_number + '" URI="#comprobante">';
    SignedInfo += '\n<ds:Transforms>';
    SignedInfo += '\n<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature">';
    SignedInfo += '</ds:Transform>';
    SignedInfo += '\n</ds:Transforms>';
    SignedInfo += '\n<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1">';
    SignedInfo += '</ds:DigestMethod>';
    SignedInfo += '\n<ds:DigestValue>';

    //HASH O DIGEST DE TODO EL ARCHIVO XML IDENTIFICADO POR EL id="comprobante"
    SignedInfo += sha1_comprobante;

    SignedInfo += '</ds:DigestValue>';
    SignedInfo += '\n</ds:Reference>';
    SignedInfo += '\n</ds:SignedInfo>';

    SignedInfo_para_firma = SignedInfo.replace('<ds:SignedInfo', '<ds:SignedInfo ' + xmlns);

    var md = forge.md.sha1.create();
    md.update(SignedInfo_para_firma, 'utf8');

    var signature = btoa(key.sign(md)).match(/.{1,76}/g).join("\n");


    var xades_bes = '';

    //INICIO DE LA FIRMA DIGITAL
    xades_bes += '<ds:Signature ' + xmlns + ' Id="Signature' + Signature_number + '">';
    xades_bes += '\n' + SignedInfo;

    xades_bes += '\n<ds:SignatureValue Id="SignatureValue' + SignatureValue_number + '">\n';

    //VALOR DE LA FIRMA (ENCRIPTADO CON LA LLAVE PRIVADA DEL CERTIFICADO DIGITAL)
    xades_bes += signature;

    xades_bes += '\n</ds:SignatureValue>';

    xades_bes += '\n' + KeyInfo;

    xades_bes += '\n<ds:Object Id="Signature' + Signature_number + '-Object' + Object_number + '">';
    xades_bes += '<etsi:QualifyingProperties Target="#Signature' + Signature_number + '">';

    //ELEMENTO <etsi:SignedProperties>';
    xades_bes += SignedProperties;

    xades_bes += '</etsi:QualifyingProperties>';
    xades_bes += '</ds:Object>';
    xades_bes += '</ds:Signature>';

    //FIN DE LA FIRMA DIGITAL

    return  comprobante.replace(/(<[^<]+)$/, xades_bes + '$1');
}

function sha1_base64(txt) {
    var md = forge.md.sha1.create();
    md.update(txt);
    //console.log('Buffer in: ', Buffer);
    //return new Buffer(md.digest().toHex(), 'hex').toString('base64');
    return new window.buffer.Buffer(md.digest().toHex(), 'hex').toString('base64');
}

function hexToBase64(str) {
    var hex = ('00' + str).slice(0 - str.length - str.length % 2);
    return btoa(String.fromCharCode.apply(null,
            hex.replace(/\r|\n/g, "").replace(/([\da-fA-F]{2}) ?/g, "0x$1 ").replace(/ +$/, "").split(" "))
            );
}

function bigint2base64(bigint) {
    var base64 = '';
    base64 = btoa(bigint.toString(16).match(/\w{2}/g).map(function (a) {
        return String.fromCharCode(parseInt(a, 16));
    }).join(""));
    base64 = base64.match(/.{1,76}/g).join("\n");
    return base64;
}

function p_obtener_aleatorio() {
    return Math.floor(Math.random() * 999000) + 990;
}
