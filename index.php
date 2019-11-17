<?php

/**
 * @package Lyrics
 * 
 * 
 * @category   Music
 * @package    Lyrics
 * @version    $Rev: 37 $
 * @license    GPL
 *
 * 
 */

$listado = new Lyrics();
echo $listado->getLyrics();


/**
 * @category   Music
 * @package    Lyrics
 * @copyright  
 * @license    
 */

class Lyrics
{

    var $debug = 0;
    var $directoriobase = ".";
    var $minSizeLyricFile = "160";
    var $mod_array = [];

    var $audio_tpl = '<center><audio controls autoplay id="audio"><source src="__mp3__" type="audio/mpeg"></center>';
    var $video_tpl = '<center><video src="__video__" type="video/mp4" autoplay repeat=true controls allowFullScreen=true width=728>__video__</video></center>';

/**
 * 
 */
function getLyrics()
{

    $search_lyrics_str = "Search Lyrics";

    if (isset($_GET["dir"])) {
        $this->directoriobase = $_GET["dir"];
        $this->directoriobase = str_replace("///", "/", $this->directoriobase);
        $this->directoriobase = str_replace("//", "/", $this->directoriobase);
        $this->directoriobase = str_replace("//", "/", $this->directoriobase);
    }

    $video_str = '<div id="lyrics-container">';

    if ($this->debug)
    {
        $video_str .= "<h1>[DEBUG MODE]</h1>";
    }

    // VIDEO
    if (isset($_GET["video"])) {
        $video_str .= str_replace("__video__", $_GET["video"], $this->video_tpl) . "<br>";
    }

    // AUDIO
    if (isset($_GET["audio"])) {
        $video_str .= str_replace("__mp3__", $_GET["audio"], $this->audio_tpl) . "<br>";
    }

    $video_str .= "</div>";

    $videos_str = "<div id='lyrics-list'>";

    // recorremos directorio recursivamente y devolvemos array
    $a_videos = $this->recur_dir($this->directoriobase);


    //while (list($key, $data) = each($a_videos))
    foreach ($a_videos as $key => $data) {

        if ((isset($data["kind"])) && ($data["kind"] == "dir")) {
            for ($i = 1; $i < $data["level"]; $i++)
                $videos_str .= ""; // REMOVED OLD 2 SPACES &nbsp;

            if ((isset($_GET['dir'])) && ($_GET['dir'])) {
                $videos_str .= "\r\n<h1><a href='index.php?dir=" . $data["path"] . "'> " . $data["name"] .
                    "</a></h1>\n"; // salto de linea en cambio de directorio
            } else {
                $videos_str .= "\r\n<h1><a href='index.php?dir=" . $data["path"] . "'> " . $data["name"] .
                    "</a></h1>\n"; // salto de linea en cambio de directorio
            }

            $videos_str = $this->recorre_dir($data["content"], $videos_str);

        } else {

            if ((isset($data["level"])) && ($data["level"] != 1)) {

                //listador final para niveles 2
                $videos_str .= "<a name=''>";

                if (strtolower($data["extension"]) == "mp3") {
                    // AUDIO - MP3
                    $videos_str .= "\n<a href=\"index.php?audio=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='mp3'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "flv") {
                    // VIDEO - FLV (DEPRECATED)
                    $videos_str .= "\n<a href=\"index.php?video=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='flv'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "mp4") {
                    // VIDEO - MP4
                    $videos_str .= "\n<a href=\"index.php?video=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='mp4'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "avi") {
                    // VIDEO - AVI
                    $videos_str .= "\n<a href=\"index.php?video=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='avi'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "flac") {
                    // AUDIO - FLAC
                    $videos_str .= "\n<a href=\"index.php?audio=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='flac'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "ogg") {
                    // AUDIO - OGG
                    $videos_str .= "\n<a href=\"index.php?audio=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='ogg'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                } elseif (strtolower($data["extension"]) == "wav") {
                    // AUDIO - WAV
                    $videos_str .= "\n<a href=\"index.php?audio=" . $data["path"] . "&dir=" . $data["folder"] .
                        "&autoplay=true\" class='wav'>" . $data["name"] . "</a><br>\r\n"; // nada del raiz sale
                }else {
                    // resto extensiones ignoradas
                }
            } else {
                /*
                // OCULTAR RAIZ
                $videos_str .= "<a name=''>";
                $videos_str .= "\n<a href=\"index.php?mp3=" . $data[path] . "&autoplay=true\">" .
                $data[name] . "</a><br>\r\n"; // nada del raiz sale
                */ }
        }
    }

    $videos_str .= "</div>";

    //TODO: MEJORAR ESTE PARCHE - parche para que pille tb mp3
    if ((isset($_GET["video"])) && ($_GET["video"] == "")) {
        $_GET["video"] = $_GET["audio"];
    }


    $flv_template = file_get_contents("template.html");
    $flv_template = str_replace("__flv_video__", $video_str, $flv_template);
    $flv_template = str_replace("__flv_videos__", $videos_str, $flv_template);
    $flv_template = str_replace("__TITLE__", substr(
    strrchr(@$_GET["video"], "//"),1,-4), $flv_template);


    // existe ficheros lyrics?
    if ((file_exists(substr(@$_GET["video"], 0, -3) . "txt")) || (file_exists(substr(@$_GET["audio"], 0, -3) . "txt"))) 
    {
        if (isset($_GET["video"])) {
            $lyrics = file_get_contents(substr($_GET["video"], 0, -3) . "txt");
        } elseif (isset($_GET["audio"])) {
            $lyrics = file_get_contents(substr($_GET["audio"], 0, -3) . "txt");
            //$lyrics =str_replace("%20"," ",$lyrics);
        }else{
            $lyrics = "";
        }

        //$lyrics="<h3>".$lyrics."</h3>";

        
        // procesamiento lyrics
        $lyrics = str_replace("<file>", "", $lyrics);
        $lyrics = str_replace("</file>", "", $lyrics);

        //$lyrics = htmlentities($lyrics);//BUG: peta y no sale nada de las letras.
        $lyrics = str_replace("�", "'", $lyrics);

        $lyrics = substr_replace("<h4>" . $lyrics . "</h5>", "</h4><h5>", strpos("<h1>" . $lyrics, "\r\n"), 4);
        
        $flv_template = str_replace("__lyrics__", "<div class='lyrics'>" . nl2br($lyrics) . "</div>", $flv_template);
        
    }
    // sino creamos el fichero con solo el titulo del mp3
    else {

        if (isset($_GET["video"]))
        {
            $cancion = substr(strrchr($_GET["video"], "//"), 1, -4);
        }elseif (isset($_GET["mp3"])) 
        {
            $cancion = substr(strrchr($_GET["audio"], "//"), 1, -4);
        }elseif (isset($_GET["dir"])) 
        {
            $cancion = substr(strrchr($_GET["dir"], "//"), 1, -4);
        }else{
            $cancion = "lyrics";
        }


        //BUG: Solucionado creo - creacion de .txt nada mas cargar el script
        if ((isset($_GET["video"])) && ($_GET["video"] != "")) {
            $this->crea_lyrics(substr($_GET["video"], 0, -4) . ".txt", "$cancion\r\n\r\n");
        }
        // si no hay parametros no crees el .txt


        $cancion = " lyrics " .$cancion;
        if (is_numeric(substr($cancion, 1, 1))) {
            $cancion = substr($cancion, 2);
        }

        $cancion = str_replace(" ", "+", trim($cancion));
        $cancion = str_replace("-", "+", $cancion);
        $cancion = str_replace("\'", "", $cancion);
        $cancion = str_replace("_", "+", $cancion);

        if ((isset($_GET["video"])) || (isset($_GET["mp3"]))) {
            $cancion = "<a href='http://www.google.com/search?client=opera&rls=es-es&q=$cancion&sourceid=opera&ie=utf-8&oe=utf-8' target='_blank'>$search_lyrics_str</a>";
            $flv_template = str_replace(
                "__lyrics__",
                "<div class='lyrics'>$cancion</div><br>",
                $flv_template
            ); // no mostrar nada al cargar
        }
        $flv_template = str_replace("__lyrics__", "", $flv_template);
    }

    // MODIFICACIONES FINALES
    //$flv_template = str_replace("?", "'", $flv_template);


    // RESULTADOS POR PANTALLA
    //echo utf8_decode($flv_template); //BUG: Removed problems with spanish folders
    echo $flv_template;

    if ($this->debug) {
        echo "<pre>";
        echo print_r($a_videos);
        echo "</pre>";
    }
}


/////////////////////////////////////////////////////////
// 						FUNCIONES
/////////////////////////////////////////////////////////


function recorre_dir($a_videos, &$videos_str)
{
    //echo "<BR>";// salto de linea en cambio de directorio
    //while (list($key, $data) = each($a_videos))
    foreach ($a_videos as $key => $data) {

        if ((isset($data["kind"])) && ($data["kind"] == "dir")) {
            $videos_str .= "\r\n";
            $videos_str .= "<h3><a href='index.php?dir=" . $data["path"] . "'>";
            for ($i = 1; $i < $data["level"]; $i++)
                $videos_str .= ""; // REMOVED OLD 4 SPACES &nbsp;
            //$videos_str.= $data[level]."\\".$data[name]."</b><br>\n";// salto de linea en cambio de directorio
            $videos_str .= $data["name"] . "</a></h3>\n";
            // salto de linea en cambio de directorio
            $videos_str = $this->recorre_dir($data["content"], $videos_str);
        } else {


            //TODO: VIDEOS-FLV
            //TODO: Change to a external validation extension function
            if ((strtolower(substr($data["name"], -3)) == "flv") || (strtolower(substr($data["name"],-3)) == "mp4")  || (strtolower(substr($data["name"],-3)) == "avi") ) {

                for ($i = 1; $i < $data["level"]; $i++)
                    $videos_str .= ""; // REMOVED OLD 2 SPACES &nbsp;
                $videos_str .= "\n<a href=\"index.php?video=" . $data["path"] . "&dir=" . $data["folder"] .
                    "\" class='flv'>" . $data["name"] . "</a>";
                $descripcion = substr($data["path"], 0, -3) . "txt";
                if (file_exists($descripcion)) {
                    if ($this->debug)
                        $videos_str .= " (" . filesize($descripcion) . " bytes)";
                    if (filesize($descripcion) < $this->minSizeLyricFile) {
                        $videos_str .= "<a href=\"$descripcion\" target=\"_blank\" class='flv_lyrics_no'> - [lyrics]</a>";
                    } else {
                        $videos_str .= "<a href=\"$descripcion\" target=\"_blank\" class='flv_lyrics_yes'> - [lyrics]</a>";
                    }
                }
                $videos_str .= "<br>\r\n";
            }


            //TODO: SONGS-MP3
            if ( (strtolower(substr($data["name"], -3)) == "mp3") || (strtolower(substr($data["name"],-3)) == "wav") || (strtolower(substr($data["name"],-3)) == "ogg")  || (strtolower(substr($data["name"],-4)) == "flac") ) {

                for ($i = 1; $i < $data["level"]; $i++)
                    $videos_str .= ""; // REMOVED OLD 2 SPACES &nbsp;
                // Si estamos listando un subdirectorio
                $videos_str .= "\n<a href=\"index.php?audio=" . $data["path"] . "&dir=" . $data["folder"] .
                    "\" class='audio'>" . $data["name"] . "</a>";
                $descripcion = substr($data["path"], 0, -3) . "txt";
                if (file_exists($descripcion)) {

                    if ($this->debug)
                        $videos_str .= " (" . filesize($descripcion) . " bytes)";
                    if (filesize($descripcion) < $this->minSizeLyricFile) {
                        $videos_str .= "<a href=\"$descripcion\" target=\"_blank\" class='mp3_lyrics_no'> - [lyrics]</a>";
                    } else {
                        $videos_str .= "<a href=\"$descripcion\" target=\"_blank\" class='mp3_lyrics_yes'> - [lyrics]</a>";
                    }
                }

                $videos_str .= "<br>\r\n";
            }
        }
    }
    return ($videos_str);
}


function recur_dir($dir)
{
    $mod_array=array();
    //        $web_root = '.';
    if (isset($_GET["dir"])) {
        $web_root = $_GET["dir"];
    } else {
        $web_root = $this->directoriobase;
    }

    $dirlist = opendir($dir);
    while ($file = readdir($dirlist)) {
        if ($file != '.' && $file != '..' && $file != '.svn') {
            $newpath = $dir . '/' . $file;
            $level = explode('/', $newpath);
            if (is_dir($newpath)) {
                $mod_array[] = array('level' => count($level) - 1, 'path' => $newpath, 'name' =>
                end($level), 'kind' => 'dir', 'mod_time' => filemtime($newpath), 'content' => $this->recur_dir($newpath));
            } else {
                // Añadido folder como la ruta hasta el fichero desde el dir base
                // quitado url (solo quitaba el ultimo punto :? - quizas para missing imagenes?

                $mod_array[] = array(
                    'level' => count($level) - 1, 'path' => $newpath, 'folder' =>
                    substr($newpath, 0, strrpos($newpath, "/") + 1), 'name' => end($level), 'kind' =>
                    'file', 'extension' => substr(end($level), strrpos(end($level), ".") + 1),
                    'mod_time' => filemtime($newpath), 'size' => filesize($newpath)
                );
            }
        }
    }
    closedir($dirlist);
    return $mod_array;
}


function crea_lyrics($fichero, $texto)
{
    //BUG: SOLUCIONADO quitando las contrabarras de texto y nombre fichero :? - Falla con los simbolos ingleses ej; can't, men's, etc...
    $fichero = str_replace("\\", "", $fichero);
    $texto = str_replace("\\", "", $texto);
    if ($desc = fopen($fichero, "w")) {
        fputs($desc, $texto);
        fclose($desc);
        //if (file_exists($fichero))	echo "Archivo ".$fichero." creado";
    }
}
}


