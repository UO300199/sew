class Memoria {

    #cartas;
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro;

    constructor() {
        this.#cartas = document.querySelectorAll("article");
        this.#reiniciarAtributos();
        this.#inicializarEventos();
        this.#barajarCartas();
        this.#tablero_bloqueado=false;
        this.#cronometro=new Cronometro();
        this.#cronometro.arrancar();
    }

    #reiniciarAtributos() {
        this.#tablero_bloqueado=true;
        this.#primera_carta=null;
        this.#segunda_carta=null;

    }

    #inicializarEventos() {
        for (let i = 0; i < this.#cartas.length; i++) {
            this.#cartas[i].addEventListener("click", (event) => this.voltearCarta(event.currentTarget));
        }
    }
   
    voltearCarta(carta) {
        if (!carta) return;
        if (carta.dataset.estado !== "revelada" && carta.dataset.estado !== "volteada" && !this.#tablero_bloqueado) {
            carta.dataset.estado = "volteada";
            if (this.#primera_carta === null) {
                this.#primera_carta = carta;
                return;
            } else {
                this.#segunda_carta = carta;
                // bloquear el tablero mientras comprobamos la pareja
                this.#tablero_bloqueado = true;
                this.#comprobarPareja();
            }
        }
    }

    #comprobarJuego() {
        for (let i = 0; i < this.#cartas.length; i++) {
            if (this.#cartas[i].dataset.estado!=="revelada") {
                return false;
            }
        }
        this.#cronometro.parar();
        return true;
    }

    
    #deshabilitarCartas() {
        this.#primera_carta.dataset.estado="revelada";
        this.#segunda_carta.dataset.estado="revelada";
        this.#comprobarJuego();
        this.#reiniciarAtributos();    
        this.#tablero_bloqueado=false;    
    }
    

    #cubrirCartas(){
        this.#tablero_bloqueado=true;
        setTimeout(() => {
        delete this.#primera_carta.dataset.estado;
        delete this.#segunda_carta.dataset.estado;
        this.#reiniciarAtributos();
        this.#tablero_bloqueado=false;
        },1500);
    }

    #comprobarPareja() {
        this.#primera_carta.children[1].getAttribute("src") === this.#segunda_carta.children[1].getAttribute("src") ? this.#deshabilitarCartas() : this.#cubrirCartas();
    }

    #barajarCartas() {
        const tablero = document.querySelector("main"); 
        
        // Convertimos la colección de cartas en un array para poder manipularlo
        let cartasArray = Array.from(this.#cartas);

        // Recorremos desde el final hasta el inicio (algoritmo Fisher–Yates)
        for (let i = cartasArray.length - 1; i > 0; i--) {
            // Generamos un índice aleatorio entre 0 e i
            let j = Math.floor(Math.random() * (i + 1));

            // Intercambiamos las cartas en el array
            [cartasArray[i], cartasArray[j]] = [cartasArray[j], cartasArray[i]];
        }

        // Reinsertamos las cartas en el DOM en el nuevo orden
        for (let i = 0; i < cartasArray.length; i++) {
            tablero.appendChild(cartasArray[i]);
        }

        //Guardamos el nuevo orden de las cartas
        this.#cartas = document.querySelectorAll("article");
    }

}

let memoria = new Memoria();