class Noticia{
    #busqueda;
    #url;
    #lsNoticias = [];

    constructor(busqueda, url) {
        this.#busqueda = busqueda;
        this.#url = url+busqueda+"&limit=3";
    }

    buscar(){
        fetch(this.#url).then(response => response.json())
        .then(data => {
            this.#procesarInformacion(data);
            this.#mostrarNoticias();
        });
    }

    #procesarInformacion(data){
        //Titular, entradilla, enlace y fuente
        for (let i = 0; i < data.data.length; i++) {
            this.#lsNoticias.push({
                titulo: data.data[i].title,
                entradilla: data.data[i].description,
                url: data.data[i].url,
                fuente: data.data[i].source,
            });
        }
    }

    #mostrarNoticias() {
        const $article = $('<article>');
        const $h2 = $('<h2>').text(`Noticias sobre ${this.#busqueda}:`);
        $article.append($h2);
        for (let i = 0; i < this.#lsNoticias.length; i++) {
            const $titulo = $('<h3>').text(this.#lsNoticias[i].titulo);
            const $entradilla = $('<p>').text(this.#lsNoticias[i].entradilla);
            const $enlace = $('<a>').attr('href', this.#lsNoticias[i].url).text(this.#lsNoticias[i].titulo);
            const $fuente = $('<p>').text(`Fuente: ${this.#lsNoticias[i].fuente}`);

            $article.append($titulo, $entradilla, $enlace, $fuente);
        }
        $('body').append($article);
    }
}

