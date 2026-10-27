# Memoria · Tarea de refuerzo UD3 «Forja de Héroes II»

**Alumno/a:** Manuel Expósito Sánchez · **Variante:** A · **Fecha:** 18 de octubre de 2026

> Esta tarea **no lleva nota**: sirve para comprobar que dominas lo que necesitarás en la UD5 (sesiones y login) y para preparar la prueba de diagnóstico del RA 3. Las capturas van en `capturas/` y se enlazan así: `![descripción](capturas/nombre.png)`

## 1. Comprobación de resultados

Compara TU página con la tabla de comprobación del enunciado.

| Dato | Valor esperado (enunciado) | Valor de mi página | ¿Coincide? |
|---|---|---|---|
| Poder total de la party | 1243 | 1243 | Sí |
| Daño por ronda | 205,6 | 205,6 | Sí |
| Líder | Aldric | Aldric | Sí |
| Vida al salir de las salas | 305 (de 774) | 305 (de 774) | Sí |
| Resultado frente al jefe (y ronda) | Victoria en la ronda 5 | Victoria en la ronda 5 | Sí |
| Oro final | 2.260,75 mo | 2.260,75 mo | Sí |
| Valor del inventario | 151,20 mo | 151,20 mo | Sí |
| Salas en el mapa | 7 | 7 | Sí |

Todos los valores coinciden exactamente con la tabla de comprobación oficial de la Variante A. Para el cálculo del poder de combate individual se redondeó cada valor con `(int) round($danio * $nivel)`, y el líder fue designado comprobando el mayor poder entre los héroes en estado activo. Las cantidades monetarias y subtotales se formatearon con la convención española (`number_format(..., 2, ',', '.')` acompañado del sufijo `mo`).

## 2. Bucles y documento resultante

![Foreach de la party en el editor](capturas/bucle-party-editor.png)
![Ctrl+U de la tabla de la party](capturas/ctrl-u-party.png)

- **En el código hay una fila `<tr>`; en el Ctrl+U, ¿cuántas? ¿Por qué?**
  En el archivo fuente `expedicion.php`, dentro del cuerpo del bucle `foreach ($party as $heroe): ... endforeach;`, está escrita **una sola fila `<tr>` de plantilla** (más la fila inicial fija con los encabezados `<th>`).
  En el documento resultante que ve el navegador a través de **Ctrl+U**, aparecen **5 filas `<tr>` en total** (1 de encabezado y 4 de datos de aventureros).
  Esto se debe a la arquitectura cliente-servidor: PHP se ejecuta íntegramente en el servidor. El bucle `foreach` se itera cuatro veces (una por cada elemento del array `$party`), evaluando las variables y emitiendo una etiqueta `<tr>` completa con los valores correspondientes en cada iteración antes de enviar el HTML final al navegador.

- **¿Aparece en el Ctrl+U algo de `endforeach` o de `if`? ¿Por qué?**
  No aparece ninguna palabra clave de PHP como `endforeach`, `if`, `else` ni `endif`.
  El intérprete del servidor procesa y consume todas las directivas de control delimitadas por `<?php ... ?>`. Una vez que el servidor termina de interpretar la lógica, produce un flujo de salida exclusivamente compuesto por texto HTML plano. El cliente web (navegador) únicamente recibe el documento final renderizado y no tiene constancia de la existencia previa del código PHP.

## 3. Experimento: predigo el efecto de un cambio

Añade esta sala **al principio** de `$salas` en tu fichero de datos:

```php
['nombre' => 'Derrumbe', 'tipo' => 'combate', 'valor' => 2400],
```

1. **Predicción** (antes de recargar):
   - **¿Cuántas salas se explorarán?** Se explorará **1 sola sala**. Al ser de tipo combate con valor 2400, la party recibe un daño de `intdiv(2400, 4) = 600` puntos. Como la vitalidad inicial del grupo en la Variante A es de 355 puntos, la vida desciende a `355 - 600 = -245`. El condicional de seguridad fija la salud en 0, añade la entrada de baja al diario y ejecuta un `break`, deteniendo el bucle de salas en su primera vuelta.
   - **¿Qué resultado tendrá el jefe?** El resultado será **Derrota · (no llegó)**. Al entrar al bloque R5 con `$vidaParty = 0`, la primera condición `if ($vidaParty <= 0)` se cumple de inmediato, asignando la derrota sin llegar a librar combate.
   - **¿Qué dirá el aviso del mapa?** Informará de una **incoherencia en el mapa**: mostrará un aviso de peligro advirtiendo que en el mapa hay 7 salas marcadas (`S`), mientras que la lista de expedición cuenta ahora con 8 salas (`count($salas) = 8`).
2. **Resultado real:**
   ![Resultado real del experimento Derrumbe](capturas/experimento-derrumbe.png)
   El resultado tras recargar coincide exactamente con lo previsto: se explora 1 de 8 salas (7 pendientes), la salud cae a 0, el combate contra el jefe no tiene lugar (Derrota · no llegó) y salta la advertencia de incoherencia en la leyenda del mapa.
3. **¿Qué sentencia ha cortado el recorrido? ¿Qué pasa con la tabla de rondas y por qué?**
   La sentencia responsable de interrumpir el recorrido es el `break;` situado tras el `switch` dentro del bloque `if ($vidaParty <= 0)`.
   La tabla de asaltos contra el jefe **no se muestra**. El requisito R7 estipula que la tabla solo se debe pintar si hubo enfrentamiento (`<?php if (!empty($rondas)): ?>`). Al haber caído el equipo antes de llegar a la guarida, la rama del combate nunca se ejecutó y la matriz `$rondas` quedó vacía.
4. Se restablecieron los datos originales de `inc/datos-A.php`.

## 4. Experimento: match frente a switch

1. **Cambia la clase de un héroe a `'Bardo'` y recarga. Captura del error. ¿Qué tipo de error es y por qué se produce?**
   ![Error fatal con clase Bardo](capturas/experimento-bardo.png)
   Se produce un error fatal no capturado:
   `Fatal error: Uncaught UnhandledMatchError: Unhandled match case 'Bardo' in .../src/expedicion.php`.
   Ocurre porque la sentencia `match` en PHP exige exhaustividad estricta. Como el requisito R3 indicó diseñar el `match` de daño sin rama `default`, al recibir una clase no contemplada (`'Bardo'`) el motor de PHP no puede resolver la expresión y lanza inmediatamente una excepción de tipo `UnhandledMatchError`.

2. **¿Qué posición le asigna el `switch` a ese Bardo? ¿Por qué el `switch` no falla y el `match` sí?**
   El `switch` le asigna la posición **`Reserva`**.
   El `switch` no falla porque dispone de una cláusula `default: $posicion = 'Reserva'; break;`, que captura de forma segura cualquier valor no enumerado en los casos anteriores. Incluso si careciera de `default`, un `switch` simplemente continuaría la ejecución omitiendo el bloque sin arrojar errores. En cambio, `match` realiza comprobaciones de identidad estricta (`===`) y no tolera casos no cubiertos en ausencia de `default`.

3. **Deja los datos como estaban. ¿Qué comportamiento prefieres en un programa real y por qué?**
   Para reglas críticas de negocio y lógica de cálculo (como fórmulas de daño según arquetipos), prefiero el comportamiento estricto de **`match` sin default**. Este enfoque 'fail-fast' evita que anomalías en los datos o fallos tipográficos pasen desapercibidos, deteniendo el flujo y forzando a corregir el problema de raíz. Para situaciones tolerantes a valores abiertos (como asignar una posición secundaria o un valor de respaldo inocuo), un `switch` con `default` o un `match` con valor de escape resulta más apropiado.
   Se restableció la clase original del héroe en `inc/datos-A.php`.

## 5. Experimento: continue dentro de switch

1. **En el caso `'vacía'`, cambia `continue 2;` por `continue;` y recarga. ¿Qué aviso aparece?**
   Aparece un aviso de advertencia:
   `Warning: "continue" targeting switch is equivalent to "break". Did you mean to use "continue 2"? in .../src/expedicion.php on line ...`.
   Desde la versión 7.3 de PHP, el uso de `continue` dentro de un `switch` emite este aviso de obsolescencia/ambigüedad.

2. **¿Qué cambia en el diario o en el recuento de salas? Explica a qué bucle afecta cada versión.**
   En PHP, la estructura `switch` se evalúa a efectos de salto de flujo como una estructura de control de nivel 1. Por lo tanto, escribir `continue;` (equivalente a `continue 1;`) solo interrumpe el propio `switch`, comportándose exactamente como un `break;`. Esto provoca que el programa continúe ejecutando el código que esté situado a continuación del `switch` dentro de la misma iteración del bucle `foreach`.
   Por el contrario, `continue 2;` le instruye a PHP saltar 2 niveles: sale del `switch` y avanza de forma inmediata a la siguiente iteración del `foreach` exterior de salas.
   En este caso práctico, como la sala vacía no altera la vida de la party, el recuento de salas y el diario no sufren variación visual directa, pero el servidor emite el `Warning`, incumpliendo el mandato de cero advertencias con `error_reporting = E_ALL`.

3. Se verificó que el código mantiene `continue 2;` como estaba originalmente.

## 6. Uso de IA (obligatorio declararlo)

¿Has usado alguna IA? ¿Para qué? Copia una respuesta que te diera y explica si era correcta o qué tuviste que corregir.

Sí, utilicé un asistente de IA como herramienta de consulta para dos aspectos técnicos de la práctica:

1. **Consulta sobre saltos de flujo anidados:**
   - *Pregunta:* "¿Por qué PHP muestra un Warning al usar `continue` dentro de un `switch` contenido en un `foreach`?"
   - *Respuesta de la IA:* "En PHP la instrucción `switch` se considera históricamente una estructura de bucle. Si utilizas `continue` dentro de un `switch`, PHP lo interpreta como un `break` para ese bloque y emite un Warning aconsejando el uso de `continue 2;` para saltar a la siguiente vuelta del bucle `foreach` padre."
   - *Valoración:* La respuesta fue completamente certera y me sirvió para entender la pista indicada en los comentarios del kit.

2. **Consulta sobre la construcción de la barra de salud:**
   - *Pregunta:* "¿Cuál es la forma más limpia en PHP de generar una barra de progreso de 20 bloques con caracteres llenos y vacíos?"
   - *Respuesta de la IA:* Me recomendó utilizar la función nativa `str_repeat('█', $llenos) . str_repeat('░', $vacios)`.
   - *Corrección manual:* Tuve que descartar y corregir dicha sugerencia porque el requisito R2 del enunciado prohíbe de forma explícita el uso de `str_repeat` ("Prohibido str_repeat"). En su lugar, implementé la construcción reglamentaria mediante un bucle `for ($b = 0; $b < BLOQUES_BARRA; $b++)` que va concatenando cada bloque individualmente.
