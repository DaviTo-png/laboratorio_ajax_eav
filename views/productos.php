<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NOVA Market | Tu tienda online</title>
<link rel="stylesheet" href="assets/css/estilos.css"><script src="assets/js/productos.js" defer></script>
</head>
<body>
<div class="topbar">Todo lo que necesitas, en un solo lugar.</div>
<header class="cabecera"><div class="contenedor cabecera-interior">
<a class="marca" href="./" aria-label="NOVA Market inicio"><span class="marca-icono">N</span><span>NOVA<span class="marca-sec">market</span></span></a>
<label class="buscador"><span aria-hidden="true">⌕</span><input type="search" id="busqueda" placeholder="¿Qué estás buscando?" aria-label="Buscar productos" maxlength="100"></label>
<span class="moneda">Perú · PEN</span>
</div></header>
<nav class="navegacion" aria-label="Categorías"><div class="contenedor nav-interior" id="categorias-nav"></div></nav>
<main class="contenedor">
<div class="ruta">Inicio / <span id="ruta-categoria">Todos los productos</span></div>
<section class="hero"><div><p class="sobrelinea">TU TIENDA ONLINE</p><h1>Encuentra lo que buscas.</h1><p>Explora tecnología, moda y mucho más.</p></div><div class="hero-deco" aria-hidden="true"><span>✦</span><span>✧</span><span>✦</span></div></section>
<div class="catalogo">
<aside class="panel-filtros" aria-label="Filtros de productos">
<div class="filtro-cabecera"><h2>Filtros</h2><button type="button" id="limpiar" class="enlace">Limpiar todo</button></div>
<label class="campo-categoria">Categoría<select id="categoria"><option value="0">Todas las categorías</option></select></label>
<section class="rango"><h3>Precio (S/)</h3><form id="form-precio"><div class="precios"><label>Desde<input id="min" type="number" min="0" max="99999999" step="0.01" placeholder="0.00"></label><label>Hasta<input id="max" type="number" min="0" max="99999999" step="0.01" placeholder="Sin límite"></label></div><button class="aplicar" type="submit">Aplicar precio</button></form></section>
<p class="ayuda-filtros">Las opciones en gris no tienen productos con los filtros actuales. El número indica las coincidencias por opción.</p>
<div id="filtros"></div>
</aside>
<section class="contenido" aria-labelledby="titulo-resultados">
<div class="resultados-cabecera"><div><h2 id="titulo-resultados">Todos los productos</h2><p id="estado" role="status" aria-live="polite">Cargando productos...</p></div><div class="herramientas"><label>Ordenar por<select id="orden"><option value="relevancia">Relevancia</option><option value="precio_asc">Menor precio</option><option value="precio_desc">Mayor precio</option></select></label><div class="modos" aria-label="Vista de resultados"><button id="vista-tarjetas" type="button" aria-pressed="true" title="Vista tarjetas">▦</button><button id="vista-tabla" type="button" aria-pressed="false" title="Vista tabla">☰</button></div></div></div>
<div id="resultados" class="productos"></div>
<div class="tabla-scroll" id="contenedor-tabla" hidden><table><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Características</th></tr></thead><tbody id="tabla-resultados"></tbody></table></div>
</section></div>
</main>
<footer><div class="contenedor pie"><strong>NOVA market</strong><span>Catálogo de demostración · Precios referenciales</span><span>© NOVA Market</span></div></footer>
</body></html>
