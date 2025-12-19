#!/usr/bin/env python3
"""
xml2altimetria.py
Lee 'circuitoEsquema.xml' y genera 'altimetria.svg'
con el perfil altimétrico (distancia acumulada vs elevación),
incluyendo líneas de referencia horizontales y verticales,
y etiquetas de ejes.
"""

import xml.etree.ElementTree as ET

class Svg(object):
    """Genera archivos SVG con líneas, polilíneas y texto"""
    def __init__(self):
        # SVG 1.1 y espacio de nombres correcto
        self.raiz = ET.Element('svg', attrib={
            'xmlns': "http://www.w3.org/2000/svg",
            'version': "1.1"
        })

    def addLine(self, x1, y1, x2, y2, stroke, stroke_width):
        ET.SubElement(self.raiz, 'line', attrib={
            'x1': str(x1), 'y1': str(y1),
            'x2': str(x2), 'y2': str(y2),
            'stroke': stroke,
            'stroke-width': str(stroke_width)
        })

    def addPolyline(self, points, stroke, stroke_width, fill):
        ET.SubElement(self.raiz, 'polyline', attrib={
            'points': points,
            'stroke': stroke,
            'stroke-width': str(stroke_width),
            'fill': fill
        })

    def addText(self, texto, x, y, transform=None, text_anchor=None):
        attrib = {
            'x': str(x), 'y': str(y)
        }
        if transform:
            attrib['transform'] = transform
        if text_anchor:
            attrib['text-anchor'] = text_anchor
        ET.SubElement(self.raiz, 'text', attrib=attrib).text = texto

    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)  # requiere Python 3.9+
        arbol.write(nombreArchivoSVG, encoding='utf-8', xml_declaration=True)


def main():
    nombreXML = input('Introduzca el nombre del archivo XML de origen: ')
    nombreSVG = input('Introduzca el nombre del archivo SVG de destino: ')

    # Namespace del XML de origen
    ns = '{http://www.uniovi.es}'

    tree = ET.parse(nombreXML)
    root = tree.getroot()

    # --- Datos del origen ---
    altitudes = []
    distancias = [0.0]

    alt_origen = root.find(f'.//{ns}origen/{ns}altitud')
    altitudes.append(float(alt_origen.text.strip()))
    altitud_unidad = alt_origen.attrib.get("unidad", "")

    # --- Recorrer tramos ---
    total = 0.0
    tramos = root.findall(f'.//{ns}tramo')

    # Detectar unidad de distancia desde el primer tramo
    distancia_unidad = ''
    if tramos:
        d_el0 = tramos[0].find(f'{ns}distancia')
        distancia_unidad = d_el0.attrib.get('unidad', '')

    for t in tramos:
        d_el = t.find(f'{ns}distancia')
        dist = float(d_el.text.strip())
        total += dist
        distancias.append(total)
        pf = t.find(f'{ns}punto_final')
        alt_el = pf.find(f'{ns}altitud')
        altitudes.append(float(alt_el.text.strip()))

    # --- Escalado para SVG ---
    ancho, alto = 900, 400
    margen = 80
    max_alt = max(altitudes)
    min_alt = min(altitudes)
    max_dist = total if total > 0 else 1.0  # evitar división por cero

    escala_x = (ancho - 2*margen) / max_dist
    escala_y = (alto - 2*margen) / (max_alt - min_alt if max_alt != min_alt else 1)

    # --- Construir puntos del perfil ---
    puntos = []
    for d, alt in zip(distancias, altitudes):
        x = margen + d * escala_x
        y = alto - margen - (alt - min_alt) * escala_y
        puntos.append(f"{round(x, 3)},{round(y, 3)}")
    puntos_str = " ".join(puntos)

    # --- Crear SVG ---
    svg = Svg()
    # Atributos de tamaño y viewBox al elemento <svg>
    svg.raiz.set('width', str(ancho))
    svg.raiz.set('height', str(alto))
    svg.raiz.set('viewBox', f"0 0 {ancho} {alto}")

    # Título
    svg.addText("Altimetría del circuito", margen + 180, 40)

    # Ejes principales
    svg.addLine(margen, margen, margen, alto - margen, "black", 2)
    svg.addLine(margen, alto - margen, ancho - margen, alto - margen, "black", 2)

    # --- Líneas horizontales (altitudes) y etiquetas ---
    for alt in sorted(set(altitudes)):
        y = alto - margen - (alt - min_alt) * escala_y
        svg.addLine(margen, y, ancho - margen, y, "dimgray", 1)
        svg.addText(f"{int(round(alt))} {altitud_unidad}", margen/2, y)

    # --- Líneas verticales (distancias) ---
    for idx in range(len(distancias)):
        hay_cambio = False
        es_final = (idx == len(distancias) - 1)
        if idx > 0 and not es_final and (altitudes[idx] != altitudes[idx-1] or altitudes[idx] != altitudes[idx+1]):
            hay_cambio = True
        if hay_cambio or es_final:
            x = margen + distancias[idx] * escala_x
            svg.addLine(x, margen, x, alto - margen, "dimgray", 1)

    # --- Perfil (polilínea) ---
    svg.addPolyline(puntos_str, "red", 3, "none")

    # --- Etiquetas de eje X (distancias) ---
    for idx in range(len(distancias)):
        hay_cambio = False
        es_final = (idx == len(distancias) - 1)
        if idx > 0 and not es_final and (altitudes[idx] != altitudes[idx-1] or altitudes[idx] != altitudes[idx+1]):
            hay_cambio = True
        if hay_cambio or es_final:
            x = margen + distancias[idx] * escala_x
            y = alto - margen + 10
            svg.addText(f"{int(round(distancias[idx]))}", x, y,
                        transform=f"rotate(90,{x},{y})",
                        text_anchor="start")

    # --- Títulos de ejes ---
    y_title_x = 20
    y_title_y = alto / 2
    svg.addText(f"Altitudes ({altitud_unidad})", y_title_x, y_title_y,
                transform=f"rotate(90,{y_title_x},{y_title_y})",
                text_anchor="middle")

    svg.addText(f"Distancias ({distancia_unidad})", ancho / 2 - 50, alto - margen + 70)

    # --- Guardar SVG ---
    svg.escribir(nombreSVG)
    print("Creado el archivo:", nombreSVG)


if __name__ == "__main__":
    main()
