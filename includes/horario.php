<?php
// Configuración de horarios
$horarios = [
    'lunes'     => ['abre' => '10:00', 'cierra' => '22:00'],
    'martes'    => ['abre' => '10:00', 'cierra' => '22:00'],
    'miércoles' => ['abre' => '10:00', 'cierra' => '22:00'],
    'jueves'    => ['abre' => '10:00', 'cierra' => '22:00'],
    'viernes'   => ['abre' => '10:00', 'cierra' => '23:00'],
    'sábado'    => ['abre' => '00:00', 'cierra' => '23:00'],
    'domingo'   => ['abre' => '10:00', 'cierra' => '18:00'],
];

// Días en español
$diasSemana = [
    'monday'    => 'lunes',
    'tuesday'   => 'martes',
    'wednesday' => 'miércoles',
    'thursday'  => 'jueves',
    'friday'    => 'viernes',
    'saturday'  => 'sábado',
    'sunday'    => 'domingo',
];

// Obtener día actual
$diaActual = $diasSemana[strtolower(date('l'))];
$horaActual = date('H:i');

// Verificar si está abierto
$abierto = false;
$mensajeHorario = '';
$badgeColor = 'danger';
$badgeTexto = 'Cerrado';
$badgeIcono = 'lock';

if(isset($horarios[$diaActual])) {
    $abre = $horarios[$diaActual]['abre'];
    $cierra = $horarios[$diaActual]['cierra'];
    
    // ¿Está cerrado todo el día? (misma hora de apertura y cierre)
    $cerradoHoy = ($abre === $cierra);
    
    if(!$cerradoHoy && $horaActual >= $abre && $horaActual < $cierra) {
        // ✅ ABIERTO AHORA
        $abierto = true;
        $badgeColor = 'success';
        $badgeTexto = 'Abierto';
        $badgeIcono = 'unlock';
        $mensajeHorario = "Cerramos a las {$cierra} hrs";
    } else {
        // ❌ CERRADO AHORA
        if($cerradoHoy) {
            $mensajeHorario = "Hoy cerrado";
        } elseif($horaActual < $abre) {
            $mensajeHorario = "Abrimos hoy a las {$abre} hrs";
        } else {
            // Buscar próximo día abierto
            $diasSiguiente = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
            $posActual = array_search($diaActual, $diasSiguiente);
            
            for($i = 1; $i <= 7; $i++) {
                $posSiguiente = ($posActual + $i) % 7;
                $diaSiguiente = $diasSiguiente[$posSiguiente];
                $horaSig = $horarios[$diaSiguiente]['abre'];
                $cierraSig = $horarios[$diaSiguiente]['cierra'];
                
                if($horaSig !== $cierraSig) {
                    if($i == 1) {
                        $mensajeHorario = "Abrimos mañana a las {$horaSig} hrs";
                    } else {
                        $mensajeHorario = "Abrimos el " . ucfirst($diaSiguiente) . " a las {$horaSig} hrs";
                    }
                    break;
                }
            }
        }
    }
} else {
    $mensajeHorario = 'Horario no disponible';
}

// Funciones
function estaAbierto() {
    global $abierto;
    return $abierto;
}

function getBadgeHorario() {
    global $badgeColor, $badgeTexto, $badgeIcono, $mensajeHorario, $abierto;
    return [
        'color' => $badgeColor,
        'texto' => $badgeTexto,
        'icono' => $badgeIcono,
        'mensaje' => $mensajeHorario,
        'abierto' => $abierto
    ];
}
?>