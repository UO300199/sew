#!/usr/bin/env python3
"""xml2html.py
Lee circuitoEsquema.xml (espacio de nombres http://www.uniovi.es) y genera:
 - InfoCircuito.html  (HTML con información del circuito, carrera y clasificación)
Uso: python3 xml2html.py
"""
import xml.etree.ElementTree as ET
import os

class Html(object):
    """
    Genera archivo HTML con información del circuito MotoGP
    Cumple con normativa de accesibilidad WCAG 2.1 nivel AAA
    """
    def __init__(self, titulo):
        """
        Crea la estructura básica del documento HTML
        """
        self.html = ET.Element('html', lang="es")
        self.head = ET.SubElement(self.html, 'head')
        
        # Título
        titulo_elem = ET.SubElement(self.head, 'title')
        titulo_elem.text = titulo
        
        # Meta tags
        ET.SubElement(self.head, 'meta', charset="UTF-8")
        ET.SubElement(self.head, 'meta', name="author", content="Julián Fernández")
        ET.SubElement(self.head, 'meta', name="description", content="Información detallada del circuito de MotoGP")
        ET.SubElement(self.head, 'meta', name="keywords", content="moto, motogp, circuito")
        ET.SubElement(self.head, 'meta', name="viewport", content="width=device-width, initial-scale=1.0")

        # Stylesheets y favicons (WCAG / diseño)
        ET.SubElement(self.head, 'link', rel="stylesheet", type="text/css", href="estilo/estilo.css")
        ET.SubElement(self.head, 'link', rel="stylesheet", type="text/css", href="estilo/layout.css")
        ET.SubElement(self.head, 'link', rel="icon", type="image/x-icon", href="multimedia/favicon-16px.ico")
        ET.SubElement(self.head, 'link', rel="icon", type="image/x-icon", href="multimedia/favicon-32px.ico")
        ET.SubElement(self.head, 'link', rel="icon", type="image/x-icon", href="multimedia/favicon.ico")
        

        # Body
        self.body = ET.SubElement(self.html, 'body')
                
        # Main
        self.contenedor = ET.SubElement(self.body, 'main')
    
    def addTitulo(self, texto, nivel=1):
        """
        Añade un título (h1-h6) al documento
        nivel: 1-6 para h1-h6
        """
        if nivel < 1 or nivel > 6:
            nivel = 1
        tag = f'h{nivel}'
        titulo = ET.SubElement(self.contenedor, tag)
        titulo.text = texto
        return titulo
    
    def addParrafo(self, texto):
        """
        Añade un párrafo al documento
        """
        p = ET.SubElement(self.contenedor, 'p')
        p.text = texto
        return p
    
    def addLista(self, tipo="ul"):
        """
        Crea una lista (ul o ol) y la devuelve para agregar elementos
        tipo: 'ul' para lista sin ordenar, 'ol' para ordenada
        """
        lista = ET.SubElement(self.contenedor, tipo)
        return lista
    
    def addElementoLista(self, lista, texto):
        """
        Añade un elemento (li) a una lista existente
        """
        li = ET.SubElement(lista, 'li')
        li.text = texto
        return li
    
    def addEnlace(self, texto, url, title):
        """
        Añade un enlace al documento
        """
        enlace = ET.SubElement(self.contenedor, 'a', href=url, title=title)
        enlace.text = texto
        return enlace
    
    def addImagen(self, src, alt):
        """
        Añade una imagen al documento con atributos de accesibilidad AAA
        """
        img = ET.SubElement(self.contenedor, 'img', src=src, alt=alt)
        return img
    
    def addVideo(self, src, type):
        """
        Añade un video al documento con múltiples fuentes
        """
        video = ET.SubElement(self.contenedor, 'video', controls="controls", preload="auto")
        
        source_mp4 = ET.SubElement(video, 'source', src=src, type=type)
                
        return video
    
    def addSeccion(self, titulo_seccion=""):
        """
        Añade una sección semántica al documento
        """
        seccion = ET.SubElement(self.contenedor, 'section')
        
        if titulo_seccion:
            h2 = ET.SubElement(seccion, 'h2')
            h2.text = titulo_seccion
        
        return seccion
    
    def addArticulo(self, titulo_articulo=""):
        """
        Añade un artículo semántico al documento
        """
        articulo = ET.SubElement(self.contenedor, 'article')
        
        if titulo_articulo:
            h3 = ET.SubElement(articulo, 'h3')
            h3.text = titulo_articulo
        
        return articulo
    
    def addTabla(self, encabezados, filas):
        """
        Añade una tabla al documento con atributos de accesibilidad AAA
        encabezados: lista de nombres de columnas
        filas: lista de listas con los datos
        """
        tabla = ET.SubElement(self.contenedor, 'table')
        
                
        # Encabezado
        thead = ET.SubElement(tabla, 'thead')
        tr_head = ET.SubElement(thead, 'tr')
        for encabezado in encabezados:
            th = ET.SubElement(tr_head, 'th', scope="col")
            th.text = encabezado
        
        # Cuerpo
        tbody = ET.SubElement(tabla, 'tbody')
        for fila in filas:
            tr = ET.SubElement(tbody, 'tr')
            for celda in fila:
                td = ET.SubElement(tr, 'td')
                td.text = str(celda)
        
        return tabla
    
    def addEnlaceEnLista(self, lista, texto, url):
        """
        Añade un elemento con enlace a una lista
        """
        li = ET.SubElement(lista, 'li')
        enlace = ET.SubElement(li, 'a', href=url)
        enlace.text = texto
        return li
    
    def escribir(self, nombreArchivoHTML):
        """
        Escribe el archivo HTML con declaración y codificación
        """
        arbol = ET.ElementTree(self.html)
        ET.indent(arbol, space="  ")
        with open(nombreArchivoHTML, 'w', encoding='utf-8') as f:
            f.write('<!DOCTYPE html>\n')
            arbol.write(f, encoding='unicode', method='html')
    
def main():
    
    nombreXML = input('Introduzca el nombre del archivo XML de origen: ')
    nombreHTML = input('Introduzca el nombre del archivo HTML de destino: ')

    nuevoHTML = Html("Información del Circuito - MotoGP")
    
    # Namespace declarado en el XML
    ns = '{http://www.uniovi.es}'
    
    tree = ET.parse(nombreXML)
    root = tree.getroot()
    
    # ===== INFORMACIÓN BÁSICA DEL CIRCUITO =====
    nombre = root.find(f'./{ns}nombre')
    nombre_circuito = nombre.text
    
    nuevoHTML.addTitulo(nombre_circuito, 1)
    
    # Información general
    longitud = root.find(f'./{ns}longitud_pista')
    anchura = root.find(f'./{ns}anchura_media')
    localidad = root.find(f'./{ns}localidad')
    pais = root.find(f'./{ns}pais')
    patrocinador = root.find(f'./{ns}patrocinador')
    
    seccion_info = nuevoHTML.addSeccion("Información General")
    
    p_long = ET.SubElement(seccion_info, 'p')
    p_long.text = f"Longitud de pista: {longitud.text} {longitud.attrib['unidad']}"

    p_anch = ET.SubElement(seccion_info, 'p')
    p_anch.text = f"Anchura media: {anchura.text} {anchura.attrib['unidad']}"

    p_loc = ET.SubElement(seccion_info, 'p')
    p_loc.text = f"Ubicación: {localidad.text}, {pais.text}"

    p_pat = ET.SubElement(seccion_info, 'p')
    p_pat.text = f"Patrocinador: {patrocinador.text}"
    
    # ===== INFORMACIÓN DE CARRERA =====
    carrera = root.find(f'./{ns}carrera')
    seccion_carrera = nuevoHTML.addSeccion("Información de Carrera")
    
    fecha = carrera.find(f'{ns}fecha_carrera')
    hora = carrera.find(f'{ns}hora_inicio')
    vueltas = carrera.find(f'{ns}vueltas')
    
    p_fecha = ET.SubElement(seccion_carrera, 'p')
    p_fecha.text = f"Fecha de carrera: {fecha.text}"

    p_hora = ET.SubElement(seccion_carrera, 'p')
    p_hora.text = f"Hora de inicio: {hora.text} ({hora.attrib['timezone']})"

    p_vueltas = ET.SubElement(seccion_carrera, 'p')
    p_vueltas.text = f"Número de vueltas: {vueltas.text}"
    
    # ===== REFERENCIAS =====
    referencias = root.find(f'./{ns}referencias')
    seccion_ref = nuevoHTML.addSeccion("Referencias")
    lista_ref = ET.SubElement(seccion_ref, 'ul')
    
    for ref in referencias.findall(f'{ns}referencia'):
        nuevoHTML.addEnlaceEnLista(lista_ref, ref.text, ref.text)
    
    # ===== GALERÍA DE FOTOS =====
    galeria_fotos = root.find(f'./{ns}galeria_fotos')
    seccion_fotos = nuevoHTML.addSeccion("Galería de Fotos")

    # Añadir cada imagen como un <img> independiente
    fotos = galeria_fotos.findall(f'{ns}foto')
    for foto in fotos:  # ✅ CORREGIDO: eliminado enumerate
        src_el = foto.find(f'{ns}src')
        src_text = src_el.text.strip()

        alt_el = foto.find(f'{ns}alt')
        if alt_el is not None and alt_el.text and alt_el.text.strip():
            alt_text = alt_el.text.strip()
        else:
            # Derivar un alt descriptivo a partir del nombre del fichero
            nombre_fich = os.path.splitext(os.path.basename(src_text))[0]
            alt_text = f"Fotografía {nombre_fich}"

        # Insertar <img> directamente en la sección (sin <figure>)
        img = ET.SubElement(seccion_fotos, 'img', src=src_text, alt=alt_text)
    
    # ===== GALERÍA DE VIDEOS =====
    galeria_videos = root.find(f'./{ns}galeria_videos')
    seccion_videos = nuevoHTML.addSeccion("Galería de Videos")
    
    videos = galeria_videos.findall(f'{ns}video')
    for video in videos:
        src = video.find(f'{ns}src')
        type_elem = video.find(f'{ns}type')
        
        video_elem = ET.SubElement(seccion_videos, 'video', controls="controls", preload="auto")
        source = ET.SubElement(video_elem, 'source', src=src.text, type=type_elem.text)
    
    # ===== RESULTADO DE CARRERA =====
    resultado = root.find(f'./{ns}resultado_carrera')
    vencedor = resultado.find(f'{ns}vencedor')
    if vencedor is not None:
        seccion_result = nuevoHTML.addSeccion("Resultado de Carrera")
        
        nombre_v = vencedor.find(f'{ns}nombre')
        equipo_v = vencedor.find(f'{ns}equipo')
        tiempo = vencedor.find(f'{ns}tiempo')
        
        p_venc = ET.SubElement(seccion_result, 'p')
        p_venc.text = f"Vencedor: {nombre_v.text}"
    
        p_equipo = ET.SubElement(seccion_result, 'p')
        p_equipo.text = f"Equipo: {equipo_v.text}"
    
        h = tiempo.find(f'{ns}horas')
        m = tiempo.find(f'{ns}minutos')
        s = tiempo.find(f'{ns}segundos')
        ms = tiempo.find(f'{ns}milisegundos')
        
        h_val = h.text
        m_val = m.text
        s_val = s.text
        ms_val = ms.text
        
        p_tiempo = ET.SubElement(seccion_result, 'p')
        p_tiempo.text = f"Tiempo: {h_val}h {m_val}m {s_val}s {ms_val}ms"
    
    # ===== CLASIFICACIÓN MUNDIAL =====
    clasificacion = root.find(f'./{ns}clasificacion_mundial_2025')
    seccion_clasif = nuevoHTML.addSeccion("Clasificación Mundial 2025")
    
    pilotos = clasificacion.findall(f'{ns}piloto')
    encabezados = ["Posición", "Piloto", "Equipo", "Puntos"]
    filas = []
    
    for piloto in pilotos:
        posicion = piloto.attrib['posicion']
        nombre_p = piloto.find(f'{ns}nombre')
        equipo_p = piloto.find(f'{ns}equipo')
        puntos_p = piloto.find(f'{ns}puntos')
        
        fila = [
            posicion,
            nombre_p.text,
            equipo_p.text,
            puntos_p.text
        ]
        filas.append(fila)
    
    tabla = ET.SubElement(seccion_clasif, 'table')
    
    thead = ET.SubElement(tabla, 'thead')
    tr_head = ET.SubElement(thead, 'tr')
    for enc in encabezados:
        th = ET.SubElement(tr_head, 'th', scope="col")
        th.text = enc
    
    tbody = ET.SubElement(tabla, 'tbody')
    for fila in filas:
        tr = ET.SubElement(tbody, 'tr')
        for celda in fila:
            td = ET.SubElement(tr, 'td')
            td.text = str(celda)

    nuevoHTML.escribir(nombreHTML)
    print(f"Creado el archivo: {nombreHTML}")

if __name__ == "__main__":
    main()