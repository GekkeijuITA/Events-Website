<?php
    session_start();
    $account = FALSE;
    $successo = FALSE;
    $passwordSbagliata= FALSE;
    $evento = $_GET["codice"];
    $totale = 0;
    $percorsoFileXml = "../dati/utenti.xml";
    if(!empty($_SESSION["email"]))
    {
        $email = $_SESSION["email"];

        $simpleXml = simplexml_load_file($percorsoFileXml);
        //controllo che l'utente sia già iscritto all'evento
        foreach($simpleXml -> children() as $simpleUtente)
        {
            if($simpleUtente['email'] == $email)
            {
                foreach($simpleUtente -> children() as $simpleEvento)
                {
                    if($simpleEvento['codice'] == $evento)
                    {
                        $account = TRUE;
                        break;
                    }
                    else
                    {
                        $account = FALSE;
                    }
                }
            }
        }

        if(isset($_POST["prenota"]))
        {
            $numPersone = $_POST["quantity"];
            $iscritto = FALSE;
            $filexml = new DOMDocument("1.0");
            $filexml -> formatOutput = TRUE;
            $filexml -> preserveWhiteSpace = FALSE;      
            $filexml -> load($percorsoFileXml);

            foreach($simpleXml -> children() as $simpleUtente)
            {
                //se esiste
                if($simpleUtente['email'] == $email)
                {
                    foreach($simpleUtente -> children() as $simpleEvento)
                    {
                        if($simpleEvento['codice'] == $evento)
                        {
                            $simpleEvento['partecipanti'] += $numPersone;
                            $filexml -> loadXML($simpleXml -> asXML());
                            $account = TRUE;
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
                if(!$iscritto)
                {
                    $eventoXml = $simpleUtente -> addChild("evento");
                    $eventoXml -> addAttribute("codice" , $evento);
                    $eventoXml -> addAttribute("partecipanti" , $numPersone);
                    $filexml -> loadXML($simpleXml -> asXML()); 
                    $account = TRUE;
                    $successo = TRUE;  
                    $iscritto = TRUE;
                }
            }
            $filexml -> save($percorsoFileXml);
        }
        if(isset($_POST["logout"]))
        {
            while(!session_unset())
            {
                session_unset();
            }
            session_destroy();
            header("location: ../index.php");
        }   
        if(isset($_POST["riepilogo"]))
        {
            header("location: riepilogo.php");
        }        
    }       
    else
    {
        if(isset($_POST["prenotaVuoto"]))
        {
            $presente = TRUE;
            $successo = FALSE;
            $iscritto = FALSE;
            $isAdmin = FALSE;
            $account = FALSE;
            $email = $_POST["username"];
            $password = $_POST["password"];
            $numPersone = $_POST["quantity"];
            $percorso = "../dati/utenti.xml";
            $percorsoAdmin = "../dati/admin.xml";

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
            if(!$isAdmin)
            {
                if(file_exists($percorso))
                {
                    $filexml = new DOMDocument("1.0");
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
                        if($simpleNome == $email)
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
                    $filexml = new DOMDocument("1.0");
                    $filexml -> formatOutput = TRUE;
                    $filexml -> preserveWhiteSpace = FALSE;
                    $radice = $filexml -> createElement("utenti");
                    $filexml -> appendChild($radice);
                }
        
                //account completamente nuovo
                if(!$account && !$passwordSbagliata && !$isAdmin)
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
                    $passwordSbagliata = FALSE;
                }
                $filexml -> save($percorso);
                $account = FALSE;
            }                           
        }
    }

    //Dinamicità pagina
    $urlImmagine;
    $descrizioneEvento;
    $titoloEvento;
    $percorsoEventiXml = "../dati/eventi.xml";
    $descrizioneImmagine;
    foreach(simplexml_load_file($percorsoEventiXml) -> children() as $eventi)
    {
        if($eventi['codice'] == $evento)
        {   
            $urlImmagine = $eventi -> immagine;
            $descrizioneEvento = $eventi -> descrizione;
            $titoloEvento = $eventi -> titolo;
            $descrizioneImmagine = $eventi -> alt;
        }
    }
?>
<!DOCTYPE>
<html>
    <head>
        <title>Eventi - <?php echo $titoloEvento; ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/cssStyle.css">   
    </head>
    <body>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">                
                <?php
                if(!empty($_SESSION["email"]))
                {
                    echo '
                        <a class="navbar-brand" href="home.php">Eventi</a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                            <img src="../img/accountLogo.png" alt="accountLogo"/>
                        </button>
                    ';
                }
                else
                {
                    echo '<a class="navbar-brand" href="../index.php">Eventi</a>';
                }
                ?>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Eventi</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                  </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                        <li class="nav-item">
                            <h1>Account</h1>
                            <?php
                                if(!empty($_SESSION["email"]))
                                {
                                    echo $_SESSION["email"];
                                } 
                            ?>           
                        </li>
                        <li>
                            <form method="post" action="">
                                <input type="submit" name="logout" class="btn btn-secondary" value="Log Out">
                                <input type="submit" name="riepilogo" class="btn btn-primary" value="I tuoi eventi">
                            </form>              
                        </li>
                    </ul>
                  </div>
                </div>
            </div>
        </nav> 
        <h1 class="ms-1"><?php echo $titoloEvento; ?></h1>
        <div style="width:50%;margin:0 auto;">
            <img src="<?php echo "../".$urlImmagine; ?>" alt="<?php echo $descrizioneImmagine; ?>">
            <label class="center">
                <?php
                    echo "<label class='description text mt-2'>$descrizioneEvento</label>";
                ?>
            </label> 
            <div class="center mt-4">
                <?php
                    //calcolo persone che partecipano all'evento
                    foreach(simplexml_load_file($percorsoFileXml) -> children() as $utenti)
                    {
                        foreach($utenti -> children() as $eventi)
                        {
                            if($eventi['codice'] == $evento)
                            {
                                $totale += $eventi['partecipanti'];
                            }
                        }
                    } 
                    echo "<label class='description' style='text-align:center;'>A questo evento partecipano&nbsp<b>".$totale."</b>&nbsppersone.</label>";
                ?>             
            </div>
            <div class="center mt-4">
                <?php                
                    if($account)
                    {
                        echo '
                        <form method="post" action="">
                            <div class="center">
                                <input type="submit" name="prenota" value="Aggiungi" class="btn btn-primary" class="center">
                            </div>
                            <div class="center">
                                <div class="verticalCenterParent"><div class="verticalCenterChild">Partecipanti: </div></div>
                                <input type="number" id="quantity" name="quantity" value="1" min="1">
                            </div>
                        </form>                        
                        ';                        
                    }
                    else
                    {
                        if(!empty($_SESSION["email"]))
                        {
                            echo '
                            <form method="post" action="">
                                <div class="center">
                                    <input type="submit" name="prenota" value="Prenota" class="btn btn-primary" class="center">
                                </div>
                                <div class="center">
                                    <div class="verticalCenterParent"><div class="verticalCenterChild">Partecipanti: </div></div>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1">
                                </div>
                            </form>                        
                            '; 
                        }
                        else
                        {
                            echo '
                                <div class="center">
                                    <button type="button" class="btn w-100 btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#loginForm">Prenota</button>
                                </div>                      
                            ';                        
                        }                        
                    }                   
                ?>            
            </div>
        </div>     
        <div class="modal fade" id="loginForm" tabindex="-1" aria-labelledby="loginFormLabel" aria-hidden="true" style="color:black;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="loginFormLabel">Account</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="">
                                <div class="modal-body">
                                    <div>
                                        <input type="email" name="username" id="username" placeholder="Username" required>
                                        <input type="password" name="password" id="password" placeholder="Password" required>
                                    </div>
                                    <div class="clear">
                                        <div class="verticalCenterParent"><div class="verticalCenterChild">Partecipanti: </div></div>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                                    </div>                                    
                                </div>
                                <div class="modal-footer">                               
                                    <input type="submit" name="prenotaVuoto" class="btn btn-primary" value="Prenota">                                 
                                </div>
                            </form>
                        </div>
                    </div>
                </div>        
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
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>           
    </body>
    <?php
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