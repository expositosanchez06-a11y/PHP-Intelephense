# Rúbrica de Autoevaluación · UD3 «Forja de Héroes II»

**Alumno:** Manuel Expósito Sánchez  
**Módulo:** 0613 DWES · 2.º DAW A  
**Variante:** A  
**Fecha:** 18 de octubre de 2026  

| Criterio de Evaluación | Requisitos | Autoevaluación | Justificación |
|---|---|:---:|---|
| **CE 3.a** Se han utilizado mecanismos de decisión en la creación de bloques de sentencias. | R3, R4, R5, R7 | **Superado** | He usado `if`, `elseif` y `else` para calcular los estados de los héroes y para ver si la party puede pelear contra el jefe. Para el daño de cada clase he usado `match` sin default y para el resultado del combate `match (true)`. Para las posiciones usé un `switch` agrupando casos y otro `switch` para los tipos de sala. En el HTML usé la sintaxis alternativa con `if` y `endif`. |
| **CE 3.b** Se han utilizado bucles y se ha verificado su funcionamiento. | R2, R4, R5, R6, R7 | **Superado** | He usado bucles `foreach` para recorrer los héroes, las salas de la mazmorra y el inventario. El combate del jefe lo hice con un `while` que va contando las rondas. Las barras de vida las hice con un bucle `for` bloque a bloque porque no se podía usar `str_repeat`. También usé `continue 2` para saltar de sala en la sala vacía y `break` cuando la vida de la party llega a 0. |
| **CE 3.c** Se han utilizado arrays para almacenar y recuperar conjuntos de datos. | R2, R4, R6, R7 | **Superado** | He trabajado con arrays asociativos y matrices tanto para los datos de los héroes como para las salas y el mapa. He usado funciones de arrays como `array_sum` y `array_column` para los totales, `in_array` con true para comprobar si hay Pícara o Clérigo, `arsort` para ordenar el inventario manteniendo las claves y `array_key_first` para el objeto más abundante. |
| **CE 3.g** Se han añadido comentarios al código. | R8 | **Superado** | He puesto el bloque PHPDoc arriba del todo con mi nombre y la variante. Luego he ido comentando cada parte explicando qué hace cada bloque y por qué, usando comentarios con `//`, con `#` y con `/* */`. |
