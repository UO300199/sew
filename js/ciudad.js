class Ciudad{
    #nombre;
    #pais;
    #gentilicio;
    #latitud;
    #longitud;
    #poblacion;
    #tiempoCarreraHora;
    #unidadesTiempoCarreraHoras;
    #tiempoCarreraDia;
    #lsTiempoEntrenoHora = [];
    #unidadesTiempoEntrenoHoras;

    constructor(nombre, pais, gentilicio) {
        this.#nombre=nombre;
        this.#gentilicio=gentilicio;
        this.#pais=pais;
        //Deberían ser privados
        //setPoblacion(latitud, longitud);
        //setCoordenadas(latitud, longitud);
    }

    setPoblacion(poblacion){
        this.#poblacion=poblacion;
    }

    setCoordenadas(latitud, longitud){
        this.#latitud=latitud;
        this.#longitud=longitud;
    }

    getNombre(){
        return this.#nombre;
    }

    getPais(){
        return this.#pais;
    }

    getGentilicioAndPoblacion(){
        return `<ul><li>Gentilicio: ${this.#gentilicio}</li><li>Poblacion: ${this.#poblacion}</li></ul>`
    }

    escribirCoordenadasPunt(){
        let uList = document.createElement('ul');
        let latItem = document.createElement('li');
        latItem.textContent = `Latitud: ${this.#latitud}`;
        let longItem = document.createElement('li');
        longItem.textContent = `Longitud: ${this.#longitud}`;
        uList.appendChild(latItem);
        uList.appendChild(longItem);
        document.body.appendChild(uList);
    }

    getMeteorologiaCarrera() {
        $.getJSON(`https://archive-api.open-meteo.com/v1/archive?latitude=${this.#latitud}&longitude=${this.#longitud}&start_date=2025-06-29&end_date=2025-06-29&daily=sunrise,sunset&hourly=temperature_2m,apparent_temperature,relative_humidity_2m,rain,wind_speed_10m,wind_direction_10m&timezone=auto`).done(data => {
            this.#procesarJSONCarrera(data);
            this.#mostrarMeteorologiaCarrera();
        })
    }

    #procesarJSONCarrera(data) {
        this.#unidadesTiempoCarreraHoras = {temperatura:data.hourly_units.temperature_2m, temperaturaAparente:data.hourly_units.apparent_temperature, humedad:data.hourly_units.relative_humidity_2m, lluvia:data.hourly_units.rain, velocidadViento:data.hourly_units.wind_speed_10m, direccionViento:data.hourly_units.wind_direction_10m};
        const horaComienzoCarrera = 14; //14:00
        this.#tiempoCarreraHora = {
            hora: data.hourly.time[horaComienzoCarrera].split("T")[1],
            temperatura: data.hourly.temperature_2m[horaComienzoCarrera],
            temperaturaAparente: data.hourly.apparent_temperature[horaComienzoCarrera],
            humedad: data.hourly.relative_humidity_2m[horaComienzoCarrera],
            lluvia: data.hourly.rain[horaComienzoCarrera],
            velocidadViento: data.hourly.wind_speed_10m[horaComienzoCarrera],
            direccionViento: data.hourly.wind_direction_10m[horaComienzoCarrera]
        };
    

        this.#tiempoCarreraDia = {amanecer: data.daily.sunrise[0].split("T")[1], atardecer: data.daily.sunset[0].split("T")[1]};
    }

    #mostrarMeteorologiaCarrera() {
        const $article = $('<article>');
        const $h2 = $('<h3>').text("Pronóstico meteorológico para el día de la carrera");
        const $pDiario = $('<p>').text("Datos para el día de la carrera:");
        const $ulDiario = $('<ul>').append($('<li>').text(`Amanecer: ${this.#tiempoCarreraDia.amanecer}`), $('<li>').text(`Atardecer: ${this.#tiempoCarreraDia.atardecer}`));
        const $pHorario = $('<p>').text("Datos horarios durante la carrera:");
        const $ulHorario = $('<ul>');

        const $hora = $('<li>').text(`Hora: ${this.#tiempoCarreraHora.hora}`);
        const $internalList = $('<ul>');
        const $tempItem = $('<li>').text(`Temperatura: ${this.#tiempoCarreraHora.temperatura} ${this.#unidadesTiempoCarreraHoras.temperatura}`);
        const $tempAparenteItem = $('<li>').text(`Sensación térmica: ${this.#tiempoCarreraHora.temperaturaAparente} ${this.#unidadesTiempoCarreraHoras.temperaturaAparente}`);
        const $humedadItem = $('<li>').text(`Humedad: ${this.#tiempoCarreraHora.humedad} ${this.#unidadesTiempoCarreraHoras.humedad}`);
        const $lluviaItem = $('<li>').text(`Lluvia: ${this.#tiempoCarreraHora.lluvia} ${this.#unidadesTiempoCarreraHoras.lluvia}`);
        const $velVientoItem = $('<li>').text(`Velocidad del Viento: ${this.#tiempoCarreraHora.velocidadViento} ${this.#unidadesTiempoCarreraHoras.velocidadViento}`);
        const $dirVientoItem = $('<li>').text(`Dirección del Viento: ${this.#tiempoCarreraHora.direccionViento} ${this.#unidadesTiempoCarreraHoras.direccionViento}`);
        $internalList.append($tempItem, $tempAparenteItem, $humedadItem, $lluviaItem, $velVientoItem, $dirVientoItem);
        $hora.append($internalList);
        $ulHorario.append($hora);
    
        $article.append($h2, $pDiario, $ulDiario, $pHorario, $ulHorario);
        $('body').append($article);

        //Nos aseguramos que los entrenos se carguen después de la carrera
        this.getMeteorologiaEntrenos();
    }

    getMeteorologiaEntrenos() {
        $.getJSON(`https://archive-api.open-meteo.com/v1/archive?latitude=${this.#latitud}&longitude=${this.#longitud}&start_date=2025-06-26&end_date=2025-06-28&hourly=temperature_2m,relative_humidity_2m,rain,wind_speed_10m&timezone=auto`).done(data => {
            this.#procesarJSONEntrenos(data);
            this.#mostrarMeteorologiaEntrenos();
        })
    }

    #procesarJSONEntrenos(data) {
        this.#unidadesTiempoEntrenoHoras = {temperatura:data.hourly_units.temperature_2m, humedad:data.hourly_units.relative_humidity_2m, lluvia:data.hourly_units.rain, velocidadViento:data.hourly_units.wind_speed_10m};
        
        let temperaturaTotal = 0;
        let humedadTotal = 0;
        let lluviaTotal = 0;
        let velocidadVientoTotal = 0;
        for (let hora=0; hora<data.hourly.time.length; hora++){
            if ((hora+1)%24 === 0&& hora!==0){
                this.#lsTiempoEntrenoHora.push({
                    dia : data.hourly.time[hora-1].split("T")[0],
                    temperatura: (temperaturaTotal/24).toFixed(2),
                    humedad: (humedadTotal/24).toFixed(2),
                    lluvia: (lluviaTotal/24).toFixed(2),
                    velocidadViento: (velocidadVientoTotal/24).toFixed(2),
                });
                temperaturaTotal = 0;
                humedadTotal = 0;
                lluviaTotal = 0;
                velocidadVientoTotal = 0;
            }
            temperaturaTotal += data.hourly.temperature_2m[hora];
            humedadTotal += data.hourly.relative_humidity_2m[hora];
            lluviaTotal += data.hourly.rain[hora];
            velocidadVientoTotal += data.hourly.wind_speed_10m[hora];
        }
    }

    #mostrarMeteorologiaEntrenos() {
        for (let dia=0; dia<3; dia++){
            const $article = $('<article>');
            const $h2 = $('<h3>').text(`Pronóstico meteorológico para el día de entreno ${this.#lsTiempoEntrenoHora[dia].dia}:`);

            const $internalList = $('<ul>');
            const $tempItem = $('<li>').text(`Temperatura: ${this.#lsTiempoEntrenoHora[dia].temperatura} ${this.#unidadesTiempoEntrenoHoras.temperatura}`);
            const $humedadItem = $('<li>').text(`Humedad: ${this.#lsTiempoEntrenoHora[dia].humedad} ${this.#unidadesTiempoEntrenoHoras.humedad}`);
            const $lluviaItem = $('<li>').text(`Lluvia: ${this.#lsTiempoEntrenoHora[dia].lluvia} ${this.#unidadesTiempoEntrenoHoras.lluvia}`);
            const $velVientoItem = $('<li>').text(`Velocidad del Viento: ${this.#lsTiempoEntrenoHora[dia].velocidadViento} ${this.#unidadesTiempoEntrenoHoras.velocidadViento}`);
            $internalList.append($tempItem, $humedadItem, $lluviaItem, $velVientoItem);
        
            $article.append($h2, $internalList);
            $('body').append($article);
        }
    }
}

let parrafoCiudad = document.createElement('p');
let ciudad = new Ciudad('Assen', 'Países Bajos', 'assenense');
ciudad.setPoblacion(66215);
ciudad.setCoordenadas(52.99470811221839, 6.5648960404138235);

parrafoCiudad.textContent = `La ciudad de ${ciudad.getNombre()} está en ${ciudad.getPais()}.`;
document.body.appendChild(parrafoCiudad);

let parrafoGentilicio = document.createElement('p');
parrafoGentilicio.textContent = "El gentilicio y la población son:";
document.body.appendChild(parrafoGentilicio);
//Debido a posibles fallos
document.body.insertAdjacentHTML("beforeend", ciudad.getGentilicioAndPoblacion());

let parrafoCoor = document.createElement('p');
parrafoCoor.textContent = 'Las coordenadas son:';
document.body.appendChild(parrafoCoor);
ciudad.escribirCoordenadasPunt();

ciudad.getMeteorologiaCarrera();
//ciudad.getMeteorologiaEntrenos();