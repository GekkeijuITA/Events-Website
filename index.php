<?php
    $presente = TRUE;
    $eventoEsiste = TRUE;
    $account = FALSE;
    $successo = FALSE;
    $passwordSbagliata= FALSE;
    $iscritto = FALSE;
    $isAdmin = FALSE;
    if(isset($_POST["submit"]))
    {
        $email = $_POST["username"];
        $evento = $_POST["evento"];
        $numPersone = $_POST["quantity"];
        $password = $_POST["password"];
        $percorso = "dati/utenti.xml";
        $percorsoEventi = "dati/eventi.xml"; 
        $percorsoAdmin = "dati/admin.xml";

        //controllo che la mail non sia quella dell'admin
        if(file_exists($percorsoAdmin))
        {
            $xml = simplexml_load_file($percorsoAdmin);
            foreach($xml -> admin as $admin)
            {
                $emailId = $admin['email'];
                $passwordId = $admin['password'];
                if($emailId == hash("md5" , $email) && $passwordId == hash("sha256" , $password))
                {
                    $isAdmin = TRUE;
                    break;
                }
            }            
        }

        if(file_exists($percorsoEventi) && !$isAdmin)
        {
            if(file_exists($percorso))
            {
                //Controllo evento
                $xmlEventi = simplexml_load_file($percorsoEventi);
                foreach($xmlEventi -> children() as $eventi)
                {
                    if($eventi['codice'] == $evento)
                    {
                        $eventoEsiste = TRUE;
                        break;
                    }
                    else
                    {
                        $eventoEsiste = FALSE;
                    }
                }
                
                $filexml = new DOMDocument("1.0" , "utf-8");
                $filexml -> formatOutput = TRUE;
                $filexml -> preserveWhiteSpace = FALSE;      
                $filexml -> load($percorso); 

                //Controllo utente
                $simpleXml = simplexml_load_file($percorso);
                foreach($simpleXml -> children() as $simpleUtente)
                {
                    $simpleNome = $simpleUtente['email'];
                    $simplePassword = $simpleUtente['password'];
                    //se esiste
                    if($simpleNome == $email && $eventoEsiste)
                    {
                        $account = TRUE;
                        if($simplePassword == hash("sha256" , $password))
                        {
                            $passwordSbagliata = FALSE;
                            //controllo che l'utente sia già iscritto all'evento
                            foreach($simpleUtente -> children() as $simpleEvento)
                            {
                                if($simpleEvento['codice'] == $evento)
                                {
                                    $simpleEvento['partecipanti'] += $numPersone;
                                    $filexml -> loadXML($simpleXml -> asXML());
                                    $successo = TRUE;
                                    $iscritto = TRUE;
                                    break;
                                }
                                else
                                {
                                    $iscritto = FALSE;                   
                                }
                            }
                        }
                        else
                        {
                            $passwordSbagliata = TRUE;
                            break;
                            //sbagliata password
                        }
                    }
                    //salvataggio dati se non iscritto a evento
                    if(!$iscritto && $account && !$passwordSbagliata)
                    {
                        $eventoXml = $simpleUtente -> addChild("evento");
                        $eventoXml -> addAttribute("codice" , $evento);
                        $eventoXml -> addAttribute("partecipanti" , $numPersone);
                        $filexml -> loadXML($simpleXml -> asXML()); 
                        $successo = TRUE; 
                        $iscritto = TRUE;
                    }
                }                                                 
            }
            else
            {
                $filexml = new DOMDocument("1.0" , "utf-8");
                $filexml -> formatOutput = TRUE;
                $filexml -> preserveWhiteSpace = FALSE;
                $radice = $filexml -> createElement("utenti");
                $filexml -> appendChild($radice);
            }

            //account completamente nuovo
            if($eventoEsiste && !$account && !$passwordSbagliata && !$isAdmin)
            {
                $utente = $filexml -> createElement("utente");
                $filexml -> documentElement -> appendChild($utente);

                $nomeUtente = $filexml -> createAttribute("email");
                $nomeUtente -> value = $email;
                $utente -> appendChild($nomeUtente);

                $simplePassword = $filexml -> createAttribute("password");
                $simplePassword -> value = hash("sha256" , $password);
                $utente -> appendChild($simplePassword);

                $eventoXml = $filexml -> createElement("evento");
                $utente -> appendChild($eventoXml);

                $codiceEvento = $filexml -> createAttribute("codice");
                $codiceEvento -> value = $evento;
                $eventoXml -> appendChild($codiceEvento);

                $partecipanti = $filexml -> createAttribute("partecipanti");
                $partecipanti -> value = $numPersone;
                $eventoXml -> appendChild($partecipanti); 

                $successo = TRUE;
            }
            $filexml -> save($percorso);
        }
    }
    if(isset($_POST["login"]))
    {
        session_start();
        $presente = TRUE;
        $passwordSbagliata= FALSE;
        $username = $_POST["username"];
        $password = $_POST["password"];
        $percorso = "dati/utenti.xml";
        $percorsoAdmin = "dati/admin.xml";
        if(file_exists($percorsoAdmin))
        {
            $xml = simplexml_load_file($percorsoAdmin);
            foreach($xml -> admin as $admin)
            {
                $emailId = $admin['email'];
                $passwordId = $admin['password'];
                if($emailId == hash("md5" , $username))
                {
                    $presente = TRUE;
                    if($passwordId == hash("sha256" , $password))
                    {
                        $passwordSbagliata = FALSE;
                        $_SESSION["email"] = $username;
                        header("location: pagine/riepilogoAdmin.php");
                        break;
                    }
                    else
                    {
                        $passwordSbagliata = TRUE;
                        break;
                    }
                }
                else
                {
                    $presente = FALSE;
                }
            }            
        }
        
        if(file_exists($percorso) && !$presente)
        {
            $xml = simplexml_load_file($percorso);
            foreach($xml -> utente as $utente)
            {
                $emailId = $utente['email'];
                $passwordId = $utente['password'];
                if($username == $emailId)
                {
                    $presente = TRUE;
                    if($passwordId == hash("sha256" , $password))
                    {
                        $passwordSbagliata = FALSE;
                        $_SESSION["email"] = $username;
                        header("location: pagine/home.php");
                        break;
                    }
                    else
                    {
                        $passwordSbagliata = TRUE;
                        break;
                    }
                }
                else
                {
                    $presente = FALSE;
                }
            }
        }
    }
?>
  
<!DOCTYPE>
<html>
    <head>
        <title>Eventi</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/cssStyle.css">      
    </head>
    <body>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Eventi</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                    <img src="img/accountLogo.png" alt="accountLogo"/>
                </button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Eventi</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <h1>Login</h1>
                            <form id="login" method="post" action="">                
                                <div>
                                    <input type="email" name="username" id="username" placeholder="Username" required>
                                    <input type="password" name="password" id="password" placeholder="Password" required>
                                </div>
                                <div class="clear">
                                    <input type="submit" name="login" id="submitButton" value="Login">
                                </div>
                            </form>            
                      </li>
                    </ul>
                  </div>
                </div>
            </div>
        </nav>
        <!--Modal Account sbagliato-->
        <div class="modal fade" id="wrongSomething" tabindex="-1" aria-labelledby="wrongSomethingLabel" aria-hidden="true" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="wrongSomethingLabel">Attenzione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Sembra che l'account non esista! Per crearne uno devi partecipare ad almeno un evento.
                    </div>
                    <div class="modal-footer">                               
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Modal evento sbagliato--> 
        <div class="modal fade" id="wrongEvent" tabindex="-1" aria-labelledby="wrongEventLabel" aria-hidden="true" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="wrongEventLabel">Attenzione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Evento <?php echo " $evento " ?> non esistente!
                    </div>
                    <div class="modal-footer">                               
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div> 
        <!--Modal successo-->
        <div class="modal fade" id="success" tabindex="-1" aria-labelledby="successLabel" aria-hidden="true" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successLabel">Successo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Registrazione avvenuta correttamente!
                    </div>
                    <div class="modal-footer">                               
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>   
        <!--Modal Password sbagliata-->
        <div class="modal fade" id="wrong" tabindex="-1" aria-labelledby="wrongLabel" aria-hidden="true" style="color:black;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="wrongLabel">Attenzione</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Password errata!
                    </div>
                    <div class="modal-footer">                               
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    </div>
                </div>
            </div>
        </div>            
        <!--Inserimento dati-->                       
        <div class="center">
            <div class="generalDiv mt-2">
                <h1>Inserisci il nome utente e il codice evento desiderato</h1>
                <form method="post" action="">
                    <input type="email" name="username" id="username" placeholder="E-mail" required>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <input class="clear" type="text" name="evento" id="evento" placeholder="Codice Evento" required>
                    <div class="clear" style="color:black;"><label>Partecipanti: </label><input type="number" id="quantity" name="quantity" value="1" min="1"></div>
                    <input type="submit" name="submit" id="submitButton">
                </form>            
            </div>                                                
        </div>
        <!--Lista Eventi-->
        <div class="row row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 g-4 ps-2 text-center mt-4 mb-1" style="max-width: 100%">
            <?php
                $percorsoFile = "dati/eventi.xml";
                if(file_exists($percorsoFile))
                {
                    $fileXml = simplexml_load_file($percorsoFile);
                    $numEventi = $fileXml -> count();
                    $eventi = $fileXml -> children();
                    for($i = 0 ; $i < $numEventi ; $i++)
                    {
                        $codice = $eventi[$i]['codice'];
                        $evento = $eventi[$i] -> children();
                        $titolo = $evento -> titolo;
                        $copertina = $evento -> immagine;
                        echo '
                        <div class="col center">
                            <div class="card h-100 bg-secondary">
                                <img src="'.$copertina.'" class="card-img-top" alt="'.$titolo.'">
                                <div class="card-body">
                                    <h5 class="card-title">'.$titolo.'</h5>
                                    <p class="card-text">Codice Evento: '.$codice.'</p>
                                    <form method="post" action="pagine/evento.php?codice='.$codice.'">
                                        <input type="submit" name="submit" value="Esplora" class="btn btn-primary">
                                    </form>                                    
                                </div>
                            </div>
                        </div> ';               
                    }          
                }
            ?>         
        </div>                              
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>           
    </body>
    <?php
        if(!$presente)
        {
            echo '<script type="text/javascript">
			$(document).ready(function(){
				$("#wrongSomething").modal("show");
			});
                  </script>';             
        }
        if(!$eventoEsiste)
        {
            echo '<script type="text/javascript">
			$(document).ready(function(){
				$("#wrongEvent").modal("show");
			});
                  </script>';             
        }
        if($successo)
        {
            echo '<script type="text/javascript">
			$(document).ready(function(){
				$("#success").modal("show");
			});
                  </script>';             
        }
        if($passwordSbagliata)
        {
            echo '<script type="text/javascript">
            $(document).ready(function(){
                $("#wrong").modal("show");
            });
                  </script>';             
        }                
    ?>
</html>