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
        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg", version="2.0")

    def addLine(self, x1, y1, x2, y2, stroke, strokeWidth):
        ET.SubElement(self.raiz, 'line',
                      x1=str(x1), y1=str(y1),
                      x2=str(x2), y2=str(y2),
                      stroke=stroke, strokeWidth=str(strokeWidth))

    def addPolyline(self, points, stroke, strokeWidth, fill):
        ET.SubElement(self.raiz, 'polyline',
                      points=points,
                      stroke=stroke,
                      strokeWidth=str(strokeWidth),
                      fill=fill)

    def addText(self, texto, x, y, fontFamily, fontSize, style):
        ET.SubElement(self.raiz, 'text',
                      x=str(x), y=str(y),
                      fontFamily=fontFamily,
                      fontSize=str(fontSize),
                      style=style).text = texto

    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoSVG, encoding='utf-8', xml_declaration=True)


def main():
    nombreXML = input('Introduzca el nombre del archivo XML de origen: ')
    nombreSVG = input('Introduzca el nombre del archivo SVG de destino: ')

    #Namespace
    ns = '{http://www.uniovi.es}'

    tree = ET.parse(nombreXML)
    root = tree.getroot()

    # --- Datos del origen ---
    altitudes = []
    distancias = [0.0]

    alt_origen = root.find(f'.//{ns}origen/{ns}altitud')
    altitudes.append(float(alt_origen.text.strip()))

    altitud_unidad = alt_origen.attrib["unidad"]

    # --- Recorrer tramos ---
    total = 0.0
    tramos = root.findall(f'.//{ns}tramo')

    # Detectar unidad de distancia desde el primer tramo
    distancia_unidad = ''
    if tramos:
        d_el0 = tramos[0].find(f'{ns}distancia')
        distancia_unidad = d_el0.attrib['unidad']
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
    max_dist = total

    escala_x = (ancho - 2*margen) / max_dist
    escala_y = (alto - 2*margen) / (max_alt - min_alt if max_alt != min_alt else 1)

    # --- Construir puntos del perfil ---
    puntos = ""
    for d, alt in zip(distancias, altitudes):
        x = margen + d * escala_x
        y = alto - margen - (alt - min_alt) * escala_y
        puntos += f"{x},{y} "

    # --- Crear SVG ---
    svg = Svg()
    # Añadir atributos de tamaño y viewBox al elemento <svg>
    svg.raiz.set('width', str(ancho))
    svg.raiz.set('height', str(alto))
    svg.raiz.set('viewBox', f"0 0 {ancho} {alto}")
    svg.addText("Altimetría del circuito", margen + 180, 40, "Sans-serif", "18", "fill:black")

    # Ejes principales
    svg.addLine(margen, margen, margen, alto - margen, "black", 2)
    svg.addLine(margen, alto - margen, ancho - margen, alto - margen, "black", 2)

    # --- Líneas horizontales (altitudes) ---
    for alt in sorted(set(altitudes)):
        y = alto - margen - (alt - min_alt) * escala_y
        svg.addLine(margen, y, ancho - margen, y, "dimgray", 1)
        svg.addText(f"{alt:.0f} {altitud_unidad}", margen/2, y, "Sans-serif", "10", "fill:black")

    # --- Líneas verticales (distancias) ---
    # Dibujamos línea vertical donde HAY cambio de altitud o en el punto final
    for idx in range(len(distancias)):
        # Verificar si hay cambio: comparar con el anterior (si existe)
        hay_cambio = False
        es_final = (idx == len(distancias) - 1)
        
        if idx > 0 and not es_final and (altitudes[idx] != altitudes[idx-1] or altitudes[idx] != altitudes[idx+1]):
            hay_cambio = True
        
        if hay_cambio or es_final:
            x = margen + distancias[idx] * escala_x
            svg.addLine(x, margen, x, alto - margen, "dimgray", 1)

    # La gráfica debe dibujarse después de los ejes
    svg.addPolyline(puntos.strip(), "red", "3", "none")

    # --- Etiquetas de eje X (distancias) ---
    # Ponemos etiqueta donde hay cambio de altitud o en el punto final
    for idx in range(len(distancias)):
        hay_cambio = False
        es_final = (idx == len(distancias) - 1)
        
        if idx > 0 and not es_final and (altitudes[idx] != altitudes[idx-1] or altitudes[idx] != altitudes[idx+1]):
            hay_cambio = True
        
        if hay_cambio or es_final:
            x = margen + distancias[idx] * escala_x
            svg.addText(f"{int(distancias[idx])}", x, alto - margen + 30,
                    "Sans-serif", "10",
                    "writing-mode: tb; glyph-orientation-vertical: 0; fill:black")

    # --- Títulos de ejes ---
    # Eje Y (vertical)
    svg.addText(f"Altitudes ({altitud_unidad})", 20, alto / 2, "Sans-serif", "14",
                "writing-mode: tb; glyph-orientation-vertical: 0; fill:black")
    # Eje X (horizontal)
    svg.addText(f"Distancias ({distancia_unidad})", ancho / 2 - 50, alto + margen/2,
                "Sans-serif", "14", "fill:black")

    # --- Guardar SVG ---
    svg.escribir(nombreSVG)
    print("Creado el archivo:", nombreSVG)


if __name__ == "__main__":
    main()