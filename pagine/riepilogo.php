<?php
    session_start();
    $utenteLoggato = $_SESSION["email"];
    if(isset($_POST["logout"]))
    {
        while(!session_unset())
        {
            session_unset();
        }
        session_destroy();
        header("location: ../index.php");
    }    
?>
<!DOCTYPE>
<html>
    <head>
        <title>Eventi - Riepilogo</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/cssStyle.css">
        <script src="js/script.js"></script>        
    </head>
    <body>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="home.php">Eventi</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
                    <img src="../img/accountLogo.png" alt="accountLogo"/>
                </button>
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
        <h1 class="ms-2 mt-2">I tuoi eventi</h1>
        <?php 
            $percorsoEventi = "../dati/eventi.xml";              
            $percorso = "../dati/utenti.xml";   
            $xml = simplexml_load_file($percorso);
            $xmlEventi = simplexml_load_file($percorsoEventi);
            foreach($xml -> utente as $utente)
            {
                $emailId = $utente['email'];
                if($utenteLoggato == $emailId)
                {
                    foreach($utente -> children() as $eventi)
                    {
                        $codice = $eventi['codice'];
                        $partecipanti = $eventi['partecipanti'];
                        echo "</br>"."<h2 class='ms-2'>"."Evento: ".$xmlEventi -> evento[$codice-1] -> titolo." "."</br>Partecipanti: ".$partecipanti."</h2>";
                        if($partecipanti>50)
                        {
                            echo " <p class='warning'><mark>Attenzione 50 o più partecipanti per questo evento!</mark></p>"."</br>";
                        }
                        else
                        {
                            echo "</br>";
                        }                        
                    }
                }
            }
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>           
    </body>
</html>