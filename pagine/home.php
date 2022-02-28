<?php
    session_start();
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
?>
<!DOCTYPE>
<html>
    <head>
        <title>Eventi</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">  
        <link rel="stylesheet" href="../css/bootstrap.min.css">
        <link rel="stylesheet" href="../css/cssStyle.css">     
    </head>
    <body>
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Eventi</a>
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
                                echo $_SESSION["email"];
                            ?>           
                        </li>
                        <li class="mt-2">
                            <form method="post" action="">                                
                                <input type="submit" name="riepilogo" class="btn btn-primary" value="I tuoi eventi">
                            </form>              
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
        <h1>Benvenuto!</h1>
        <!--Lista Eventi-->
        <div class="row row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 g-4 ps-2 text-center mt-4 mb-1" style="max-width: 100%">
            <?php
                $percorsoFile = "../dati/eventi.xml";
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
                                <img src="../'.$copertina.'" class="card-img-top" alt="'.$titolo.'">
                                <div class="card-body">
                                    <h5 class="card-title">'.$titolo.'</h5>
                                    <p class="card-text">Codice Evento: '.$codice.'</p>
                                    <form method="post" action="evento.php?codice='.$codice.'">
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
        <script src="../js/bootstrap.min.js"></script>           
    </body>
</html>