<style>
    h1, b {
        font-family: "sansbook";
    }

    #container.responsive-page{
        min-height: 200px !important;
        margin-bottom: 10px !important;
    }
    #footer{
        position: absolute;
        width: 80%;
    }
    /* para 400px 0 menos*/
    @media screen and (max-height:800px) {
        #footer{
            position: inherit !important;
            width: 100%;
        }
        .data-section{
            min-height: 100px !important;
            margin-bottom: 40px;
        }
    }
    /* para 700px o menos */
    @media screen and (max-width:1000px) {
        body {
            margin: 0 auto;
            max-width: 1360px;
            min-width: 400px !important;
            padding: 0;
        }
    }
</style>
<div class="title-page terms-section">
    <?php if ($language == IS_ESPANIOL) { ?>
        Ayuda
    <?php } else { ?>
        Help
    <?php } ?>
</div>
<div class="full-page">
    <div class="data-page">        
        <div class="data-section">
            <div class="terms-data">
                <?php if ($language == IS_ESPANIOL) { ?>
                    <h1>Si no tengo una cuenta de usuario, ¿qu&eacute; puedo hacer?</h1>
                    <p>Para ingresar es ESENCIAL tener un nombre de usuario y una contrase&ntilde;a que s&oacute;lo un contacto de ContiTech CBG y sus Distribuidores pueden proporcionar.</p>

                        <h1>¿Qu&eacute; pasa si pierdo mis datos de acceso?</h1>
                        <p>Le sugerimos mantener los datos de acceso en un archivo adicional dentro de su equipo, no s&oacute;lo en el mail de bienvenida. En caso de no encontrar el acceso solicite a su Distribuidor o ejecutivo en ContiTech que regenere la contrase&ntilde;a.</p>
                        <p>La nueva contrase&ntilde;a se enviar&aacute; al correo electr&oacute;nico registrado en su perfil.</p>

                            <h1>Si el sistema no me permite almacenar informaci&oacute;n, ¿qu&eacute; puedo hacer?</h1>
                        <p>Cualquier falla t&eacute;cnica la puede reportar desde el formulario de contacto seleccionando la opci&oacute;n Soporte Contiplus o report&aacute;ndolo a <a href="mailto:admin@contiplus.net">admin@contiplus.net</a></p>

                        <p><b>* Todo comentario o sugerencia la puede enviar desde el formulario de contacto o directo al correo <a href="mailto:admin@contiplus.net">admin@contiplus.net</a></b></p>

                <?php } else { ?>
                    <!-- ENGLISH VERSION -->
                    <h1>If I do not have a user account, what can I do?</h1>
                    <p>To enter is ESSENTIAL to have a username and password that only a contact from ContiTech CBG division and their Distributors can provide.</p>
                    <p>Please require an access to your authorized contact.</p>
                    <br>
                    <h1>What happens if I lose my access data?</h1>
                    <p>We suggest you keep your access data in an additional file in your computer, not only on the welcome mail. In case you are not able to find your information, you can request to your Distributor or ContiTech executive to regenerate your password.</p>
                    <p>The new password will be sent to you to the email you registered in your profile.</p>
                    <br>
                    <h1>If the system doesn’t allow me to store information, what can I do?</h1>
                    <p>Any technical failure can be reported to <a href="mailto:admin@contiplus.net">admin@contiplus.net</a></p>
                    <br>
                    <p><b>* All comments or suggestions can be sent to the email address <a href="mailto:admin@contiplus.net">admin@contiplus.net</a></b></p>


                <?php } ?>
            </div>
        </div>
    </div>
</div>