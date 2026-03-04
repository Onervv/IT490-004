<?php
/**
    @brief gets the currrent machine information and optionally any other task
    specific INI folder installed in the system path.
    Uses the default path of /var/system_ini/ unless $INIPATH is set

    @return parsed ini machine information
*/
function getHostInfo(array $extra = NULL)
{
    $machine = array();
    
    // Try to load host.ini - first from current directory, then from include directory
    $host_ini_paths = [
        "host.ini",
        __DIR__ . "/../config/host.ini"
    ];
    
    foreach ($host_ini_paths as $path) {
        if (file_exists($path)) {
            $parsed = parse_ini_file($path, true);
            if ($parsed !== false) {
                $machine = array_merge($machine, $parsed);
                break;
            }
        }
    }
    
    if ($extra != NULL)
    {
        foreach ($extra as $ini)
        {
            $parsed = parse_ini_file($ini,true);
            if ($parsed)
            {
                $machine = array_merge($machine,$parsed);
            }
        }
    }
    return $machine;
}

?>

