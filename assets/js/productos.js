const $ = id => document.getElementById(id);
const categoria = $('categoria'), busqueda = $('busqueda'), filtros = $('filtros');
const resultados = $('resultados'), estado = $('estado'), tabla = $('tabla-resultados');
const min = $('min'), max = $('max'), orden = $('orden');
let temporizador, versionBusqueda = 0;
let rango = { min: '', max: '' };
const moneda = new Intl.NumberFormat('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

async function consultar(accion, parametros = new URLSearchParams()) {
    parametros.set('accion', accion);
    const respuesta = await fetch('index.php?' + parametros.toString(), { cache: 'no-store' });
    const datos = await respuesta.json();
    if (!respuesta.ok) throw new Error(datos.error || 'Error al consultar el servidor.');
    return datos;
}
function elemento(tipo, texto, clase) {
    const nodo = document.createElement(tipo);
    if (texto !== undefined) nodo.textContent = texto;
    if (clase) nodo.className = clase;
    return nodo;
}
function mostrarError(error) {
    resultados.replaceChildren(); tabla.replaceChildren();
    estado.classList.add('error'); estado.textContent = error.message || 'Error de conexión.';
}
async function cargarCategorias() {
    const datos = await consultar('apiGetCategorias');
    const nav = $('categorias-nav');
    [{id: 0, nombre: 'Todas las categorías'}, ...datos].forEach(c => {
        if (Number(c.id) !== 0) {
            const opcion = elemento('option', c.nombre); opcion.value = c.id; categoria.append(opcion);
        }
        const boton = elemento('button', c.nombre); boton.type = 'button'; boton.dataset.id = c.id;
        boton.setAttribute('aria-pressed', String(Number(c.id) === 0));
        boton.addEventListener('click', () => { categoria.value = c.id; cargarFiltros(); });
        nav.append(boton);
    });
}
async function cargarFiltros() {
    clearTimeout(temporizador);
    const nombre = categoria.value === '0' ? 'Todos los productos' : categoria.selectedOptions[0].textContent;
    $('titulo-resultados').textContent = nombre; $('ruta-categoria').textContent = nombre;
    $('categorias-nav').querySelectorAll('button').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.id === categoria.value)));
    filtros.replaceChildren(); resultados.replaceChildren(); tabla.replaceChildren();
    await buscarProductos();
}
function renderizarFiltros(datos) {
    if (!filtros.children.length) {
        datos.forEach((a, i) => {
            const grupo = elemento('details', undefined, 'grupo'); grupo.open = categoria.value !== '0' || i < 2;
            grupo.append(elemento('summary', a.nombre));
            const opciones = elemento('div', undefined, 'opciones');
            a.valores.forEach(opcion => {
                const etiqueta = elemento('label');
                const check = elemento('input'); check.type = 'checkbox'; check.value = opcion.valor; check.dataset.atributo = a.id;
                check.addEventListener('change', buscarProductos);
                etiqueta.append(check, elemento('span', opcion.valor, 'valor-filtro'), elemento('span', '', 'cantidad-filtro')); opciones.append(etiqueta);
            });
            grupo.append(opciones); filtros.append(grupo);
        });
    }
    const cantidades = new Map(datos.map(a => [String(a.id), new Map(a.valores.map(o => [o.valor, o.cantidad]))]));
    filtros.querySelectorAll('input').forEach(check => {
        const cantidad = cantidades.get(check.dataset.atributo)?.get(check.value) ?? 0;
        check.disabled = cantidad === 0 && !check.checked;
        const etiqueta = check.closest('label');
        etiqueta.classList.toggle('no-disponible', check.disabled);
        etiqueta.querySelector('.cantidad-filtro').textContent = '(' + cantidad + ')';
        etiqueta.title = check.disabled ? 'No hay productos con esta combinación' : '';
    });
    filtros.removeAttribute('aria-busy');
}
function bloquearPendientes() {
    filtros.setAttribute('aria-busy', 'true');
    filtros.querySelectorAll('input:not(:checked)').forEach(check => check.disabled = true);
}
async function buscarProductos() {
    clearTimeout(temporizador);
    const version = ++versionBusqueda;
    const parametros = new URLSearchParams({categoria_id: categoria.value, q: busqueda.value.trim(), orden: orden.value, ...rango});
    // OR dentro de un atributo, AND entre atributos distintos.
    filtros.querySelectorAll('input:checked').forEach(check => parametros.append(`filtros[${check.dataset.atributo}][]`, check.value));
    bloquearPendientes();
    estado.classList.remove('error'); estado.textContent = 'Buscando...';
    try {
        const [productos, disponibilidad] = await Promise.all([
            consultar('apiBuscar', new URLSearchParams(parametros)),
            consultar('apiGetFiltros', new URLSearchParams(parametros))
        ]);
        if (version !== versionBusqueda) return;
        renderizarFiltros(disponibilidad);
        renderizarProductos(productos);
    } catch (error) {
        if (version === versionBusqueda) {
            filtros.removeAttribute('aria-busy');
            mostrarError(error);
        }
    }
}
function renderizarProductos(productos) {
    resultados.replaceChildren(); tabla.replaceChildren();
    estado.textContent = productos.length + (productos.length === 1 ? ' producto encontrado' : ' productos encontrados');
    if (!productos.length) {
        resultados.append(elemento('p', 'No encontramos productos. Prueba con otros filtros.', 'sin-resultados'));
        const celda = tabla.insertRow().insertCell(); celda.colSpan = 4; celda.textContent = 'No se encontraron productos.'; return;
    }
    productos.forEach(p => {
        const card = elemento('article', undefined, 'producto');
        const foto = elemento('img', undefined, 'foto'); foto.alt = p.nombre; foto.loading = 'lazy';
        // Solo nombres locales permitidos; los textos se renderizan con textContent.
        foto.src = 'assets/img/' + (/^producto-\d+\.svg$/.test(p.imagen) ? p.imagen : 'producto-0.svg');
        const detalle = elemento('div', undefined, 'detalle');
        const caracteristicas = p.atributos.map(a => a.nombre + ': ' + a.valor).join(' · ');
        const precio = 'S/ ' + moneda.format(p.precio);
        detalle.append(elemento('p', p.categoria, 'categoria-producto'), elemento('h3', p.nombre), elemento('p', caracteristicas, 'atributos'), elemento('p', precio, 'precio'));
        card.append(foto, detalle); resultados.append(card);
        const fila = tabla.insertRow(); [p.nombre, p.categoria, precio, caracteristicas].forEach(v => fila.insertCell().textContent = v);
    });
}
categoria.addEventListener('change', cargarFiltros);
busqueda.addEventListener('input', () => {
    clearTimeout(temporizador); ++versionBusqueda; bloquearPendientes();
    temporizador = setTimeout(buscarProductos, 400);
});
orden.addEventListener('change', buscarProductos);
$('form-precio').addEventListener('submit', e => {
    e.preventDefault();
    if (min.value !== '' && max.value !== '' && Number(min.value) > Number(max.value)) {
        max.setCustomValidity('El máximo debe ser mayor o igual al mínimo.'); max.reportValidity(); return;
    }
    rango = {min: min.value === '' ? '' : Number(min.value).toFixed(2), max: max.value === '' ? '' : Number(max.value).toFixed(2)};
    buscarProductos();
});
[min,max].forEach(campo => campo.addEventListener('input', () => max.setCustomValidity('')));
$('limpiar').addEventListener('click', () => {
    categoria.value = '0'; busqueda.value = ''; min.value = ''; max.value = ''; max.setCustomValidity(''); rango = {min:'',max:''}; orden.value = 'relevancia'; cargarFiltros();
});
function cambiarVista(tabular) {
    resultados.hidden = tabular; $('contenedor-tabla').hidden = !tabular;
    $('vista-tabla').setAttribute('aria-pressed', String(tabular)); $('vista-tarjetas').setAttribute('aria-pressed', String(!tabular));
}
$('vista-tabla').addEventListener('click', () => cambiarVista(true));
$('vista-tarjetas').addEventListener('click', () => cambiarVista(false));
async function iniciar() { try { await cargarCategorias(); await cargarFiltros(); } catch (error) { mostrarError(error); } }
iniciar();
