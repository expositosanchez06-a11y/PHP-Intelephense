# Rúbrica de Autoevaluación · UD3 «Forja de Héroes II»

**Alumno:** Manuel Expósito Sánchez  
**Módulo:** 0613 DWES · 2.º DAW A  
**Variante:** A · La Cripta de los Ecos  
**Fecha:** 18 de octubre de 2026  

| Criterio de Evaluación | Requisitos demostrados | Autoevaluación | Justificación del alumno |
|---|---|:---:|---|
| **CE 3.a** Se han utilizado mecanismos de decisión en la creación de bloques de sentencias. | R3, R4, R5, R7 | **Superado** | Uso riguroso de `if/elseif/else` para clasificar estados vitales y condiciones de acceso al jefe, `match` estricto sin default para el cálculo de daño por clase, `match (true)` para determinar el resultado del combate, `switch` con casos agrupados para formación táctica y para el efecto de cada sala, y sintaxis alternativa en la vista HTML (`if: ... else: ... endif;`). |
| **CE 3.b** Se han utilizado bucles y se ha verificado su funcionamiento. | R2, R4, R5, R6, R7 | **Superado** | Empleo de bucles `foreach` para recorrer la party, las salas, el inventario y las celdas del mapa; bucle `while` para los asaltos de combate contra el jefe; bucles `for` (sin `str_repeat`) para construir las barras de vitalidad y recorrer la matriz 2D del mapa; uso adecuado de `continue 2;` en salas vacías y `break;` ante la caída del equipo; verificado mediante los experimentos de la memoria (puntos 2, 3 y 5). |
| **CE 3.c** Se han utilizado arrays para almacenar y recuperar conjuntos de datos. | R2, R4, R6, R7 | **Superado** | Manejo de matrices indexadas de arrays asociativos (party y salas), matrices bidimensionales (mapa), arrays asociativos (inventario y precios), y aplicación de funciones nativas de manipulación (`array_sum`, `array_column`, `in_array` estricto, `array_key_exists`, `arsort` conservando claves y `array_key_first`). |
| **CE 3.g** Se han añadido comentarios al código. | R8 | **Superado** | Documentación de cabecera en formato PHPDoc con autor y variante, comentarios por bloque explicando el propósito funcional y la justificación de diseño (qué y por qué), empleando los formatos `//`, `#` y `/* ... */` sin incurrir en atributos `#[`. |
