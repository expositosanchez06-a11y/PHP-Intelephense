<?php
/**
 * =====================================================================
 *  expedicion.php · Forja de Héroes II · La party y la mazmorra
 * =====================================================================
 *  0613 DWES · UD3 · Tarea de refuerzo (sin nota)
 *  Autor/a: Manuel Expósito Sánchez · Variante: A
 *
 *  Informe de expedición sala a sala y combate contra el jefe final.
 *  Cálculos procesados en servidor en cada petición sin persistencia.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/reglas.php';
require_once __DIR__ . '/inc/datos-' . VARIANTE . '.php';

// =====================================================================
// R2 y R3 · PROCESAMIENTO Y ESTADÍSTICAS DE LA PARTY
// =====================================================================

// Recorremos cada héroe para calcular nivel, vida máxima, daño y estado de salud
foreach ($party as $i => $heroe) {
    // El nivel aumenta cada 400 puntos de experiencia acumulados desde nivel 1
    $nivel = intdiv($heroe['xp'], XP_POR_NIVEL) + 1;

    // La vida máxima escala según constitución, nivel y el multiplicador de clase
    $vidaMax = VIDA_BASE + ($heroe['constitucion'] * $nivel * MULT_VIDA[$heroe['clase']]);

    // Porcentaje de vitalidad actual respecto al máximo
    $pctVida = ($heroe['vida'] / $vidaMax) * 100;

    // R3: Cálculo del daño mediante match estricto sin caso por defecto
    $danio = match ($heroe['clase']) {
        'Guerrera'  => ($heroe['fuerza'] ** 2) / 10 + $nivel * 2,
        'Hechicero' => $heroe['inteligencia'] * 3 + ($nivel ** 2) / 4,
        'Pícara'    => $heroe['destreza'] * 2 + ($nivel ** 1.5),
        'Clérigo'   => ($heroe['inteligencia'] + $heroe['constitucion']) * 1.5 + $nivel,
    };

    // R3: Determinación del estado vital con estructura if / elseif / else
    if ($heroe['vida'] <= 0) {
        $estado = 'K.O.';
    } elseif ($pctVida < 35) {
        $estado = 'En peligro';
    } elseif ($pctVida < 70) {
        $estado = 'Herido';
    } else {
        $estado = 'En pie';
    }

    // R3: Asignación de la posición táctica con switch y casos agrupados
    switch ($heroe['clase']) {
        case 'Guerrera':
        case 'Clérigo':
            $posicion = 'Primera línea';
            break;
        case 'Pícara':
            $posicion = 'Flanco';
            break;
        case 'Hechicero':
            $posicion = 'Retaguardia';
            break;
        default:
            $posicion = 'Reserva';
            break;
    }

    // Poder de combate estimado combinando daño y nivel
    $poder = (int) round($danio * $nivel);

    // Barra visual de 20 caracteres construida con bucle for (sin str_repeat)
    $bloquesLlenos = (int) round(($pctVida / 100) * BLOQUES_BARRA);
    $bloquesLlenos = max(0, min(BLOQUES_BARRA, $bloquesLlenos));

    $barra = '';
    for ($b = 0; $b < BLOQUES_BARRA; $b++) {
        if ($b < $bloquesLlenos) {
            $barra .= '█';
        } else {
            $barra .= '░';
        }
    }

    // Guardamos los datos procesados en la propia matriz del equipo
    $party[$i]['nivel']    = $nivel;
    $party[$i]['vidaMax']  = $vidaMax;
    $party[$i]['pctVida']  = $pctVida;
    $party[$i]['danio']    = $danio;
    $party[$i]['poder']    = $poder;
    $party[$i]['estado']   = $estado;
    $party[$i]['posicion'] = $posicion;
    $party[$i]['barra']    = $barra;
}

// Totales de la party calculados mediante funciones de arrays nativas
$vidaParty    = array_sum(array_column($party, 'vida'));
$vidaMaxParty = array_sum(array_column($party, 'vidaMax'));
$poderParty   = array_sum(array_column($party, 'poder'));
$nivelMedio   = array_sum(array_column($party, 'nivel')) / count($party);

# Segundo recorrido: agregamos estadísticas de héroes activos y designamos líder
$danioParty    = 0.0;
$heroesActivos = 0;
$lider         = null;

foreach ($party as $heroe) {
    // Si el aventurero está fuera de combate, se salta con continue
    if ($heroe['estado'] === 'K.O.') {
        continue;
    }

    $danioParty += $heroe['danio'];
    $heroesActivos++;

    // El héroe activo con mayor poder asume el liderazgo del grupo
    if ($lider === null || $heroe['poder'] > $lider['poder']) {
        $lider = $heroe;
    }
}

// Comprobamos la disponibilidad de clases clave para habilidades grupales
$clasesParty = array_column($party, 'clase');
$hayPicara   = in_array('Pícara', $clasesParty, true);
$hayClerigo  = in_array('Clérigo', $clasesParty, true);

// =====================================================================
// R4 · RECORRIDO DE LA MAZMORRA (SALA A SALA)
// =====================================================================

$oro             = $oroInicial;
$xpGanada        = 0;
$diario          = [];
$salasExploradas = 0;

foreach ($salas as $n => $sala) {
    $salasExploradas++;
    $numSala = $n + 1;

    // Evaluamos el efecto de cada sala según su tipología
    switch ($sala['tipo']) {
        case 'vacía':
            $diario[] = "Sala {$numSala} · {$sala['nombre']}: vacía, la party sigue avanzando.";
            // continue 2 salta directamente a la siguiente iteración del bucle foreach
            continue 2;

        case 'trampa':
            // La presencia de una pícara neutraliza las trampas sin coste de salud
            if ($hayPicara) {
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: ¡trampa evitada por la Pícara!";
            } else {
                $vidaParty -= $sala['valor'];
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: ¡trampa! −{$sala['valor']} de vida.";
            }
            break;

        case 'combate':
            // Los enfrentamientos restan vitalidad y conceden experiencia al grupo
            $danioCombate = intdiv($sala['valor'], 4);
            $vidaParty -= $danioCombate;
            $xpGanada += $sala['valor'];
            $diario[] = "Sala {$numSala} · {$sala['nombre']}: combate, −{$danioCombate} de vida y +{$sala['valor']} XP.";
            break;

        case 'tesoro':
            // Se ingresa el oro y se almacena el posible objeto hallado
            $oro += $sala['valor'];
            if (isset($sala['objeto'])) {
                $objeto = $sala['objeto'];
                $inventario[$objeto] = ($inventario[$objeto] ?? 0) + 1;
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: +{$sala['valor']} mo y «{$objeto}».";
            } else {
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: +{$sala['valor']} mo.";
            }
            break;

        case 'descanso':
            // La presencia de clérigo duplica el alivio de salud recuperado
            $curacion = $hayClerigo ? $sala['valor'] * 2 : $sala['valor'];
            $vidaParty = min($vidaMaxParty, $vidaParty + $curacion);
            if ($hayClerigo) {
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: descanso, +{$curacion} de vida (bendición del Clérigo).";
            } else {
                $diario[] = "Sala {$numSala} · {$sala['nombre']}: descanso, +{$curacion} de vida.";
            }
            break;
    }

    // Si la vitalidad del grupo se extingue, se registra la baja y finaliza la marcha
    if ($vidaParty <= 0) {
        $vidaParty = 0;
        $diario[] = "La party ha caído en combate. La expedición termina aquí.";
        break;
    }
}

// Calculamos las salas pendientes al concluir la exploración
$salasPendientes = count($salas) - $salasExploradas;
$vidaPartyTrasSalas = $vidaParty;

// =====================================================================
// R5 · EL JEFE FINAL
// =====================================================================

$golpeParty = (int) round($danioParty);
$tieneLlave = array_key_exists($jefe['llave'], $inventario);

$rondas           = [];
$resultadoCombate = '';
$motivoCombate    = '';

/* Condición de acceso a la guarida: si la party llega con vida y posee la llave,
   se disputa el combate por asaltos contra el jefe final. */
if ($vidaParty <= 0) {
    $resultadoCombate = 'Derrota';
    $motivoCombate    = '(no llegó)';
} elseif (!$tieneLlave) {
    $resultadoCombate = 'Puerta sellada';
    $motivoCombate    = 'la party no tiene la llave';
} else {
    $vidaJefe = $jefe['vida'];
    $ronda    = 0;

    // Bucle de asaltos simultáneos hasta que uno caiga o venza el tiempo límite
    while ($vidaJefe > 0 && $vidaParty > 0 && $ronda < MAX_RONDAS) {
        $ronda++;

        // La party asesta su golpe en primer lugar
        $vidaJefe = max(0, $vidaJefe - $golpeParty);

        // Si el jefe sigue con vida, contraataca a la party
        if ($vidaJefe > 0) {
            $vidaParty = max(0, $vidaParty - $jefe['ataque']);
        }

        $rondas[] = [
            'ronda'     => $ronda,
            'vidaJefe'  => $vidaJefe,
            'vidaParty' => $vidaParty,
        ];
    }

    // Veredicto del combate mediante match sobre condiciones verdaderas
    $resultadoCombate = match (true) {
        $vidaJefe === 0  => 'Victoria',
        $vidaParty === 0 => 'Derrota',
        default          => 'Retirada',
    };

    if ($resultadoCombate === 'Victoria') {
        $oro += $jefe['recompensa'];
        $motivoCombate = "en la ronda {$ronda}";
    } elseif ($resultadoCombate === 'Retirada') {
        $motivoCombate = "({$ronda} rondas)";
    } else {
        $motivoCombate = "en la ronda {$ronda}";
    }
}

// =====================================================================
// R4 (cont.) · INVENTARIO FINAL
// =====================================================================

// Valoración económica acumulada de los suministros y tesoros restantes
$valorInventario = 0.0;
foreach ($inventario as $objeto => $cantidad) {
    $precio = $precios[$objeto] ?? 0.0;
    $valorInventario += $cantidad * $precio;
}

$totalObjetos = array_sum($inventario);

// Ordenación descendente conservando las asociaciones de claves originales
$inventarioOrdenado = $inventario;
arsort($inventarioOrdenado);

$objetoMasAbundante = array_key_first($inventarioOrdenado) ?? 'Ninguno';

// =====================================================================
// R6 · EL MAPA Y ESTADO FINAL
// =====================================================================

$conteoCeldas = [];
$totalFilas   = count($mapa);

// Recuento de casillas mediante bucles for anidados con índices numéricos
for ($f = 0; $f < $totalFilas; $f++) {
    $totalColumnas = count($mapa[$f]);
    for ($c = 0; $c < $totalColumnas; $c++) {
        $celda = $mapa[$f][$c];
        $conteoCeldas[$celda] = ($conteoCeldas[$celda] ?? 0) + 1;
    }
}

// Comprobación de integridad entre salas en mapa y listado de la expedición
$numSalasMapa  = $conteoCeldas['S'] ?? 0;
$mapaCoherente = ($numSalasMapa === count($salas));

// Barra de salud final de la party tras todas las contiendas
$pctVidaFinal   = $vidaMaxParty > 0 ? ($vidaParty / $vidaMaxParty) * 100 : 0;
$bloquesFinales = (int) round(($pctVidaFinal / 100) * BLOQUES_BARRA);
$bloquesFinales = max(0, min(BLOQUES_BARRA, $bloquesFinales));

$barraVidaParty = '';
for ($b = 0; $b < BLOQUES_BARRA; $b++) {
    if ($b < $bloquesFinales) {
        $barraVidaParty .= '█';
    } else {
        $barraVidaParty .= '░';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expedición · <?= htmlspecialchars($nombreMazmorra) ?> · Forja de Héroes II</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
<main>
    <span class="insignia">VARIANTE <?= VARIANTE ?></span>
    <h1><?= htmlspecialchars($nombreMazmorra) ?></h1>
    <p class="subtitulo">Informe de expedición · líder: <?= $lider !== null ? htmlspecialchars($lider['nombre']) : 'Ninguno' ?></p>

    <h2>La party</h2>
    <table>
        <tr><th>Héroe</th><th>Clase</th><th>Nivel</th><th>Vida</th><th>Daño</th><th>Poder</th><th>Posición</th><th>Estado</th></tr>
        <?php foreach ($party as $heroe): ?>
            <tr>
                <td><?= htmlspecialchars($heroe['nombre']) ?></td>
                <td><?= htmlspecialchars($heroe['clase']) ?></td>
                <td><?= $heroe['nivel'] ?></td>
                <td><span class="barra mini"><?= $heroe['barra'] ?></span> <?= $heroe['vida'] ?>/<?= $heroe['vidaMax'] ?></td>
                <td><?= number_format($heroe['danio'], 1, ',', '.') ?></td>
                <td><?= $heroe['poder'] ?></td>
                <td><?= htmlspecialchars($heroe['posicion']) ?></td>
                <?php if ($heroe['estado'] === 'K.O.' || $heroe['estado'] === 'En peligro'): ?>
                    <td class="peligro"><?= htmlspecialchars($heroe['estado']) ?></td>
                <?php else: ?>
                    <td class="vida"><?= htmlspecialchars($heroe['estado']) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="rejilla">
        <div class="stat"><div class="etq">Poder total</div><div class="val"><?= $poderParty ?></div></div>
        <div class="stat"><div class="etq">Nivel medio</div><div class="val"><?= number_format($nivelMedio, 1, ',', '.') ?></div></div>
        <div class="stat"><div class="etq">Daño por ronda</div><div class="val"><?= number_format($danioParty, 1, ',', '.') ?></div></div>
        <div class="stat"><div class="etq">Héroes activos</div><div class="val"><?= $heroesActivos ?> / <?= count($party) ?></div></div>
    </div>

    <h2>Diario de la expedición</h2>
    <ol class="diario">
        <?php foreach ($diario as $linea): ?>
            <li><?= htmlspecialchars($linea) ?></li>
        <?php endforeach; ?>
    </ol>
    <p>Salas exploradas: <?= $salasExploradas ?> de <?= count($salas) ?>
        <?php if ($salasPendientes > 0): ?>
            <span class="peligro">(quedan <?= $salasPendientes ?> pendientes)</span>
        <?php endif; ?>
        · XP ganada: <?= $xpGanada ?></p>

    <h2>El jefe: <?= htmlspecialchars($jefe['nombre']) ?></h2>
    <p>Vida <?= $jefe['vida'] ?> · ataque <?= $jefe['ataque'] ?> · golpe de la party: <?= $golpeParty ?> por ronda</p>
    <?php if (!empty($rondas)): ?>
        <table>
            <tr><th>Ronda</th><th>Vida del jefe</th><th>Vida de la party</th></tr>
            <?php foreach ($rondas as $r): ?>
                <tr>
                    <td>Ronda <?= $r['ronda'] ?></td>
                    <td><?= $r['vidaJefe'] ?></td>
                    <td><?= $r['vidaParty'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    <p class="resultado <?= $resultadoCombate === 'Victoria' ? 'vida' : 'peligro' ?>">
        <strong><?= htmlspecialchars($resultadoCombate) ?></strong> · <?= htmlspecialchars($motivoCombate) ?>
    </p>
    <p class="barra <?= $vidaParty > 0 ? 'vida' : 'peligro' ?>">PARTY <?= $barraVidaParty ?> <?= $vidaParty ?>/<?= $vidaMaxParty ?></p>

    <h2>Inventario</h2>
    <table>
        <tr><th>Objeto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
        <?php foreach ($inventarioOrdenado as $objeto => $cantidad): ?>
            <?php
            $precio = $precios[$objeto] ?? 0.0;
            $subtotal = $cantidad * $precio;
            ?>
            <tr>
                <td><?= htmlspecialchars($objeto) ?></td>
                <td><?= $cantidad ?></td>
                <td><?= number_format($precio, 2, ',', '.') ?> mo</td>
                <td><?= number_format($subtotal, 2, ',', '.') ?> mo</td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="rejilla">
        <div class="stat"><div class="etq">Objetos</div><div class="val"><?= $totalObjetos ?></div></div>
        <div class="stat"><div class="etq">Valor del inventario</div><div class="val"><?= number_format($valorInventario, 2, ',', '.') ?> mo</div></div>
        <div class="stat"><div class="etq">Oro final</div><div class="val"><?= number_format($oro, 2, ',', '.') ?> mo</div></div>
        <div class="stat"><div class="etq">Más abundante</div><div class="val peq"><?= htmlspecialchars($objetoMasAbundante) ?></div></div>
    </div>

    <h2>Mapa de la mazmorra</h2>
    <table class="mapa">
        <?php foreach ($mapa as $fila): ?>
            <tr>
                <?php foreach ($fila as $celda): ?>
                    <?php $claseCss = LEYENDA_MAPA[$celda][0] ?? 'desconocida'; ?>
                    <td class="<?= $claseCss ?>"><?= htmlspecialchars($celda) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>
        <?php foreach (LEYENDA_MAPA as $simbolo => $info): ?>
            <code><?= htmlspecialchars($simbolo) ?></code> <?= htmlspecialchars($info[1]) ?>: <?= $conteoCeldas[$simbolo] ?? 0 ?> &nbsp;
        <?php endforeach; ?>
    </p>
    <?php if ($mapaCoherente): ?>
        <p class="vida">✓ El mapa es coherente: hay <?= $numSalasMapa ?> salas marcadas (S) para las <?= count($salas) ?> salas de la expedición.</p>
    <?php else: ?>
        <p class="peligro">⚠ Incoherencia en el mapa: se encontraron <?= $numSalasMapa ?> salas marcadas (S), pero la expedición contiene <?= count($salas) ?> salas.</p>
    <?php endif; ?>

    <footer>Informe generado el <?= date('d/m/Y') ?> con PHP <?= PHP_VERSION ?> · cada recarga lo recalcula todo desde cero.</footer>
</main>
</body>
</html>
