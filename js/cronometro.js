class Cronometro {
    #tiempo;
    #corriendo;
    #inicio;

    constructor(){
        this.#tiempo=0;
        this.#corriendo=null;
        this.#inicializarEventos();
    }

    #inicializarEventos() {
        let botones = document.querySelectorAll("button");
        if (botones.length<3) return;
        botones[0].addEventListener("click", this.arrancar.bind(this));
        botones[1].addEventListener("click", this.parar.bind(this));
        botones[2].addEventListener("click", this.reiniciar.bind(this));
    }

    parar() {
        clearInterval(this.#corriendo);
        this.#corriendo=null;
        this.#mostrar();
    }

    #actualizar() {
        let actual;
        try {
            actual=Temporal.Now.instant().epochMilliseconds;
        } catch (err) {
            actual=Date.now();
        }
        this.#tiempo=actual-this.#inicio;
        this.#mostrar();
    }

    reiniciar(){
        clearInterval(this.#corriendo);
        this.#corriendo=null;
        this.#tiempo=0;
        this.#mostrar();
    }

    arrancar() {
        // Solo inicia si no hay un intervalo ya en ejecución
        if (this.#corriendo !== null) {
            return;
        }

        let actual;
        try {
            actual=Temporal.Now.instant().epochMilliseconds;
        } catch (err) {
            actual=Date.now();
        }
        this.#inicio=actual-this.#tiempo;

        this.#corriendo = setInterval(this.#actualizar.bind(this),100)
    }

    #mostrar() {
        let minutos = parseInt(this.#tiempo / 60000);
        let resto = this.#tiempo % 60000;
        let segundos = parseInt(resto / 1000);

        let milisegundos = resto % 1000;
        let decima = milisegundos.toString()[0];

        let str = `${minutos.toString().padStart(2,'0')}:` +
                  `${segundos.toString().padStart(2,'0')}.` +
                  `${decima}`;

        let parrafoTiempo = document.querySelector("main p");
        parrafoTiempo.textContent = str;
    }
}

let cronometro = new Cronometro();