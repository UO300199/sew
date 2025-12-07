class Carrusel {
    #busqueda;
    #actual;
    #maximo;
    #fotos;
    #imagen;
    #noticia;

    constructor(busqueda) {
        this.#busqueda=busqueda;
        this.#actual=0;
        this.#maximo=4;
        this.#fotos=[];
        //Inicializamos la clase Noticia para cargar las noticias después de las fotos
        const busquedaNews = "MotoGP";
        const url = "https://api.thenewsapi.com/v1/news/top?api_token=3GMYeDT1ojOoDwbJ4MuJnS9q90ZZ8c0nDxAWrR6j&locale=es&search="
        this.#noticia = new Noticia(busquedaNews,url);
    }

    // Realiza la petición a la API pública de Flickr (feeds) y devuelve una Promise
    getFotografias() {
        const flickrAPI = "https://api.flickr.com/services/feeds/photos_public.gne?jsoncallback=?";

        $.getJSON(flickrAPI, {
            tags: this.#busqueda,
            tagmode: "any",
            format: "json"
        })
        .done(data => {

            // Cambiar tamaño de todas las imágenes a _z
            $.each(data.items, (i, item) => {
                item.media.m = item.media.m.replace("_m.", "_z.");
            });

            this.#procesarJSONFotografias(data);
            this.#mostrarFotografias();
            //De esta manera nos aseguramos que las noticias se cargan después de las fotos
            this.#noticia.buscar();
        });
    }



    // Extrae información de 5 fotografías distintas del objeto JSON
    #procesarJSONFotografias(data) {
        let lsImgs = [];
        for (let i in data.items) {
            if (i>this.#maximo) {
                break;
            }
            lsImgs.push({src:data.items[i].media.m, alt:data.items[i].title});
        }

        this.#fotos = lsImgs;
        
    }

    // Inserta en el documento la primera de las fotografías procesadas
    #mostrarFotografias() {
        const $article = $('<article>');
        const $h2 = $('<h2>').text(`Imágenes del circuito de ${this.#busqueda}`);
        this.#imagen = $('<img>')
            .attr('src', this.#fotos[0]?.src)
            .attr('alt', this.#fotos[0]?.alt);

        $article.append($h2, this.#imagen);
        $('body').append($article);

        setInterval(this.#cambiarFotografia.bind(this), 3000);
    }

    #cambiarFotografia() {
        this.#actual++;
        if (this.#actual > this.#maximo) {
            this.#actual = 0;
        }
        this.#imagen.attr('src', this.#fotos[this.#actual]?.src)
            .attr('alt', this.#fotos[this.#actual]?.alt);
    }
    
}


let carrusel = new Carrusel('TT Circuit Assen');
carrusel.getFotografias();