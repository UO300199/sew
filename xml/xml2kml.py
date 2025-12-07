#!/usr/bin/env python3
"""xml2kml.py
Lee '/mnt/data/circuitoEsquema.xml' (espacio de nombres http://www.uniovi.es) y genera:
 - /mnt/data/circuito.kml  (KML con LineString que une el origen y los puntos finales de cada tramo)
Uso: python3 xml2kml.py
"""
import xml.etree.ElementTree as ET

class Kml(object):
    """
    Genera archivo KML con puntos y líneas
    """
    def __init__(self):
        """
        Crea el elemento raíz y el espacio de nombres
        """
        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz,'Document')

    def addPlacemark(self,nombre,descripcion,long,lat,alt, modoAltitud):
        """
        Añade un elemento <Placemark> con puntos <Point>
        """
        pm = ET.SubElement(self.doc,'Placemark')
        ET.SubElement(pm,'name').text = nombre
        ET.SubElement(pm,'description').text = descripcion
        punto = ET.SubElement(pm,'Point')
        ET.SubElement(punto,'coordinates').text = '{},{},{}'.format(long,lat,alt)
        ET.SubElement(punto,'altitudeMode').text = modoAltitud

    def addLineString(self,nombre,extrude,tesela, listaCoordenadas, modoAltitud, color, ancho):
        """
        Añade un elemento <Placemark> con líneas <LineString>
        """
        ET.SubElement(self.doc,'name').text = nombre
        pm = ET.SubElement(self.doc,'Placemark')
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls,'extrude').text = extrude
        ET.SubElement(ls,'tessellation').text = tesela
        ET.SubElement(ls,'coordinates').text = listaCoordenadas
        ET.SubElement(ls,'altitudeMode').text = modoAltitud 

        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement (linea, 'color').text = color
        ET.SubElement (linea, 'width').text = ancho

    def escribir(self,nombreArchivoKML):
        """
        Escribe el archivo KML con declaración y codificación
        """
        arbol = ET.ElementTree(self.raiz)
        """
        Introduce indentacióon y saltos de línea
        para generar XML en modo texto
        """
        ET.indent(arbol)
        arbol.write(nombreArchivoKML, encoding='utf-8', xml_declaration=True)
    
    def ver(self):
        """
        Muestra el archivo KML. Se utiliza para depurar
        """
        print("\nElemento raiz = ", self.raiz.tag)

        if self.raiz.text != None:
            print("Contenido = "    , self.raiz.text.strip('\n')) #strip() elimina los '\n' del string
        else:
            print("Contenido = "    , self.raiz.text)
        
        print("Atributos = "    , self.raiz.attrib)

        # Recorrido de los elementos del árbol
        for hijo in self.raiz.findall('.//'): # Expresión XPath
            print("\nElemento = " , hijo.tag)
            if hijo.text != None:
                print("Contenido = ", hijo.text.strip('\n')) #strip() elimina los '\n' del string
            else:
                print("Contenido = ", hijo.text)    
            print("Atributos = ", hijo.attrib)

def main():
    
    nombreXML = input('Introduzca el nombre del archivo XML de origen: ')
    nombreKML = input('Introduzca el nombre del archivo KML de destino: ')

    nuevoKML = Kml()
    # Namespace declarado en el XML
    ns = '{http://www.uniovi.es}'
    tree = ET.parse(nombreXML)
    root = tree.getroot()

    # Obtenemos el nombre del circuito
    nombre = root.find(f'./{ns}nombre')
    nombre_circuit = f"Circuito {nombre.text}"

    # origen
    origen = root.find(f'./{ns}origen')
    origen_lon = origen.find(f'{ns}longitud').text.strip()
    origen_lat = origen.find(f'{ns}latitud').text.strip()

    nuevoKML.addPlacemark("Linea de salida","Punto origen del circuito",origen_lon,origen_lat,0,'relativeToGround')

    # Recolectar puntos finales de los tramos en orden
    tramos = root.findall(f'.//{ns}tramo')  # XPath con prefijo
    coords = []
    # añadir el origen como primer punto
    coords.append((origen_lon, origen_lat, 0))

    lastSector = '1'
    for t in tramos:
        pf = t.find(f'{ns}punto_final')
        if pf is None:
            continue
        lon = pf.find(f'{ns}longitud').text.strip()
        lat = pf.find(f'{ns}latitud').text.strip()
        
        coords.append((lon, lat, 0))
        

    # Construir string de coordenadas KML
    kml_coords = ""
    for coord in coords:
        kml_coords += f"{coord[0]},{coord[1]},{coord[2]}\n"

    nuevoKML.addLineString(nombre_circuit, "1", "1", kml_coords, 'relativeToGround', '#ff0000ff', "5")
    nuevoKML.escribir(nombreKML)
    print("Creado el archivo: ", nombreKML)
      
if __name__ == "__main__":
    main()