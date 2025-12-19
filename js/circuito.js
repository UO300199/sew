class Circuito {
    
    constructor(){
        this.#comprobarApiFile();
        this.#inicializarEventos();
    }

    #comprobarApiFile(){
        if (!(window.File && window.FileReader && window.FileList && window.Blob))
        {  
            //El navegador no soporta el API File
            const mensaje = document.createElement("p");
            mensaje.textContent= "¡¡¡ Este navegador NO soporta el API File y este programa puede no funcionar correctamente !!!";
            document.body.appendChild(mensaje);
        }
    }
    #inicializarEventos() {
        const inputFile = document.querySelectorAll('input');
        inputFile[0].addEventListener('change', (evento) => {
            this.leerArchivoHTML(evento.target.files);
        });
    }

    leerArchivoHTML(files){
        this.#comprobarApiFile();
        //Solamente toma un archivo
        var archivo = files[0];
        //Solamente admite archivos de tipo texto
        var tipoTexto = /text\/html/;

        if (archivo.type.match(tipoTexto)) {
            var lector = new FileReader();
            lector.onload = (evento => {
                    this.#procesarHTMl(evento.target.result);
            })
            lector.readAsText(archivo);
        }
        else {
            alert("¡El archivo seleccionado no es de tipo HTML!");
        }       
    }

    #procesarHTMl(html) {

        // 1. Parseamos el HTML usando DOMParser (clase document)
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, "text/html");

        // 2. Bajar nivel de encabezados usando métodos del DOM
        doc.querySelectorAll("h1, h2, h3, h4, h5, h6").forEach(el => {

            const nivelActual = parseInt(el.tagName.substring(1));
            const nuevoNivel = Math.min(nivelActual + 2, 6);

            const nuevo = document.createElement("h" + nuevoNivel);
            nuevo.innerHTML = el.innerHTML;

            el.replaceWith(nuevo);
        });

        // 3. Obtener el <main> leído del archivo
        const main = doc.querySelector("main");

        if (!main) {
            console.warn("El documento cargado no contiene <main>.");
            return;
        }
        const htmlSection = document.querySelector("main>section:nth-of-type(1)");
        let article = htmlSection.querySelector("article");
        if (article===null){ 
            article = document.createElement("article");
        }
        article.innerHTML = main.innerHTML;


        htmlSection.appendChild(article);
    }

}
class CargadorSVG{
    constructor(){
        this.#inicializarEventos();
    }
    #inicializarEventos() {
        const inputFile = document.querySelectorAll('input');
        inputFile[1].addEventListener('change', (evento) => {
            this.leerArchivoSVG(evento.target.files);
        });
    }

    leerArchivoSVG(files){
        const archivo = files[0];
        if (archivo && archivo.type === 'image/svg+xml') {
            const lector = new FileReader();
            lector.onload = (e) => this.#insertarSVG(e.target.result);
            lector.readAsText(archivo);
        } else {
            alert('Selecciona un archivo SVG válido.');
        }
    }
    #insertarSVG(contenidoTexto){
        const parser = new DOMParser();
        const documentoSVG = parser.parseFromString(contenidoTexto, 'image/svg+xml');
        //Cambia la version del SVG a 1.1
        const elementoSVG = documentoSVG.documentElement;
        elementoSVG.setAttribute('version', '1.1');
        const svgSection = document.querySelector("main>section:nth-of-type(2)");
        let article = svgSection.querySelector("article");
        if (article===null){ 
            article = document.createElement("article");
        }
        /*
        elementoSVG.removeAttribute('width');
        elementoSVG.removeAttribute('height');
        elementoSVG.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        */
        article.innerHTML = "";
        const tituloSVG = document.createElement("h4");
        tituloSVG.textContent = "Altimetría del circuito";
        article.appendChild(tituloSVG);
        article.appendChild(elementoSVG);


        // Inserta después de <main>, esto es así para no tener problemas con el mapa
        svgSection.appendChild(article);
    }
}
class CargadorKML{

    #origen;
    #puntos = [];
    
    constructor(){
        this.#inicializarEventos();
    }
    
    #inicializarEventos() {
        const inputFile = document.querySelectorAll('input');
        inputFile[2].addEventListener('change', (evento) => {
            this.leerArchivoKML(evento.target.files);
        });
    }
    
    leerArchivoKML(files) {
        const archivo = files[0];
        // Verificamos que exista y que sea KML
        if (archivo && archivo.type === 'application/vnd.google-earth.kml+xml') {
            const lector = new FileReader();
            lector.onload = (e) => {this.#procesarKML(e.target.result);
                this.#insertarCapaKML();};
            lector.readAsText(archivo);
        } else {
            alert('Selecciona un archivo KML válido.');
        }
    }
    
    #procesarKML(contenidoTexto){
        const parser = new DOMParser();
        const documentoKML = parser.parseFromString(contenidoTexto, 'text/xml');
        //Punto origen
        const puntoOrigen = documentoKML.querySelector('Point > coordinates');
        this.#origen = {longitud: puntoOrigen.textContent.split(',')[0], latitud: puntoOrigen.textContent.split(',')[1]};
        
        const trazo = documentoKML.querySelector('LineString > coordinates');
        const coords = trazo.textContent.trim().split(/\s+/);
        coords.forEach(coord => {
            this.#puntos.push({ longitud: coord.split(',')[0], latitud: coord.split(',')[1] });
        });
        console.log("Origen:", this.#origen);
        console.log("Puntos:", this.#puntos);
    }
    
    #insertarCapaKML(){
        mapboxgl.accessToken = 'pk.eyJ1IjoidW8zMDAxOTkiLCJhIjoiY21pdnc2cjZnMGk1ODNlczl0OW80cGZrYSJ9.TGkvSlMH1eDF_S0zTUhCww';
        const contenedor = document.querySelector('body > main > section > div');

        const map = new mapboxgl.Map({
            container: contenedor, // aquí pasamos el nodo DOM
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [this.#origen.longitud, this.#origen.latitud], // Madrid
            zoom: 13 
        });

        const lineaMeta = new mapboxgl.Marker()
        .setLngLat([this.#origen.longitud, this.#origen.latitud])
        .addTo(map);

        // Añadir una polilínea con los puntos del KML
        const coordinates = this.#puntos.map(p => [parseFloat(p.longitud), parseFloat(p.latitud)]);

        map.on('load', () => {
            if (coordinates.length > 0) {
                // Añadimos la fuente GeoJSON con la LineString
                map.addSource('kml-line', {
                    type: 'geojson',
                    data: {
                        type: 'Feature',
                        geometry: {
                            type: 'LineString',
                            coordinates: coordinates
                        }
                    }
                });

                // Añadimos la capa de línea
                map.addLayer({
                    id: 'kml-line',
                    type: 'line',
                    source: 'kml-line',
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': '#ff0000',
                        'line-width': 4
                    }
                });

                
            }
        });

        const sectionMap = document.querySelector("main>section:nth-of-type(3)");

        sectionMap.append(contenedor);

    }
}

const circuito = new Circuito();
const cargadorSVG = new CargadorSVG();
const cargadorKML = new CargadorKML();