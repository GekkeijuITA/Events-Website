<?php
    session_start();
    $utenteLoggato = $_SESSION["email"];
    $esiste = FALSE;
    if(isset($_POST["logout"]))
    {
        while(!session_unset())
        {
            session_unset();
        }
        session_destroy();
        header("location: ../index.php");
    }
    if(isset($_POST["submit"]))
    {
        $pathFile = "../dati/eventi.xml";
        $titolo = $_POST["titolo"];

        //Controllo esistenza evento
        $fileXml = simplexml_load_file($pathFile);
        $temp = strtolower(str_replace(' ' , '' , $titolo));
        foreach($fileXml -> children() as $eventi)
        {
            $titoloXml = strtolower(str_replace(' ' , '' , $eventi -> titolo));
            if($titoloXml == $temp)
            {
                $esiste = TRUE;
                break;
            }
        }

        if(!$esiste)
        {
            $ext = (pathinfo($_FILES['foto']['name']))['extension'];
            $folder = 'img/'.strtolower(str_replace(' ' , '' , $titolo)).".".$ext;
            move_uploaded_file($_FILES['foto']['tmp_name'] , "../".$folder);
      
            $descrizione = $_POST["descrizione"];
    
            
            
            $codice = ($fileXml -> count()) + 1;
    
            $fileXml = new DOMDocument("1.0" , "utf-8");
            $fileXml -> formatOutput = true;
            $fileXml -> preserveWhiteSpace = false;
            $fileXml -> load($pathFile);
    
            $event = $fileXml -> createElement("evento");
            $code = $fileXml -> createAttribute("codice");
            $code -> value = $codice;
            $event -> appendChild($code);
            $eventTitle = $fileXml -> createElement("titolo" , $titolo);
            $eventImage = $fileXml -> createElement("immagine" , $folder);
            $eventDescription = $fileXml -> createElement("descrizione" , $descrizione);
    
            $fileXml -> documentElement -> appendChild($event);
            $event -> appendChild($eventTitle);
            $event -> appendChild($eventImage);
            $event -> appendChild($eventDescription);
    
            $fileXml -> save($pathFile);
        }

    }  
?>
<!DOCTYPE>
<html>
    <head>
        <title>Admin - Aggiungi Evento</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/cssStyle.css">
        <script src="../js/script.js"></script>        
    </head>
    <body>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="riepilogoAdmin.php">Admin</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                    <img src="../img/accountLogo.png" alt="accountLogo"/>
                </button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Admin</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <h1>Account</h1>
                            <?php
                                echo $utenteLoggato;
                            ?>           
                        </li>
                        <li>
                            <form method="post" action="">
                                <input type="submit" name="logout" class="btn btn-secondary" value="Log Out">
                            </form>              
                        </li>
                    </ul>
                  </div>
                </div>
            </div>
        </nav>
        <div class="center mt-2">
            <div class="center">
                <form class="menu" method="post" action="" enctype="multipart/form-data">
                    <input type="text" name="titolo" id="titolo" placeholder="Titolo" required>
                    <textarea id="descrizione" name="descrizione" rows="4" cols="50" class="mt-1"></textarea>
                    <input type="file" name="foto" id="foto" placeholder="Inserisci Foto" accept="image/*" onchange="loadFile(event)" class="mt-1" required>
                    <img id="output" class="mt-1"/>
                    <input type="submit" name="submit" value="Inserisci" class="mt-1">
                </form> 
            </div> 
        </div> 
        <!--Modal Evento esistente-->
        <div class="modal fade" id="exist" tabindex="-1" aria-labelledby="existLabel" aria-hidden="true" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="existLabel">Attenzione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Evento già esistente!
                    </div>
                    <div class="modal-footer">                               
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>                
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script> 
        <script>
            var loadFile = function(event) {
                var image = document.getElementById('output');
                image.src = URL.createObjectURL(event.target.files[0]);
            };                      
        </script>                         
    </body>
    <?php
            if($esiste)
            {
                echo '<script type="text/javascript">
                $(document).ready(function(){
                    $("#exist").modal("show");
                });
                    </script>';             
            }
    ?>     
</html>