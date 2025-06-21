@echo off
REM Crear estructura de carpetas
mkdir inventario
mkdir inventario\includes
mkdir inventario\assets
mkdir inventario\assets\css
mkdir inventario\assets\js
mkdir inventario\vendor

REM Crear archivos en /includes/
type nul > inventario\includes\config.php
type nul > inventario\includes\db.php
type nul > inventario\includes\functions.php

REM Crear archivos en /assets/css/
type nul > inventario\assets\css\style.css

REM Crear archivos en /assets/js/
type nul > inventario\assets\js\script.js

REM Crear archivos raíz
type nul > inventario\index.php
type nul > inventario\agregar.php
type nul > inventario\editar.php
type nul > inventario\eliminar.php
type nul > inventario\ver.php
type nul > inventario\categorias.php
type nul > inventario\exportar-excel.php
type nul > inventario\exportar-pdf.php
type nul > inventario\stock-bajo.php

echo Estructura completa creada con carpetas y archivos vacíos.
pause
