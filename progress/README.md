# Progress Memory

Este directorio contiene el estado actual del desarrollo del proyecto IESH Lanz siguiendo Spec-Driven Development.

## Archivos

- `current.json` — Tarea actual en progreso
- `history.md` — Historial de tareas completadas
- `README.md` — Este archivo

## Cómo funciona

1. El agente implementer lee `current.json` para saber qué tarea está en progreso
2. Lee `features.json` para ver tareas pendientes
3. Lee `SPEC.md` como fuente de verdad
4. Implementa la tarea
5. Actualiza `current.json` y `history.md`

## Estado Actual

Ver `progress/current.json` para el estado actual.