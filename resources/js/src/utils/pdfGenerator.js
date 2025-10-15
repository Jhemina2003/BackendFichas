export const pdfGenerator = {
  generarTicket(turno) {
    // Crear contenido del ticket con formato similar al PDF
    const contenido = `
MINISTERIO DE RELACIONES EXTERIORES
DIRECCIÓN DEPARTAMENTAL DE LA PAZ

${turno.codigo}

Espere su turno
${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}

Tiempo máximo de espera 15 minutos

Servicio: ${turno.servicio}
Tipo: ${turno.tipo}
Número: ${turno.numero}
    `.trim()

    // Simular descarga de PDF
    console.log('Generando PDF con contenido:', contenido)

    // En una implementación real, usaríamos una librería como jsPDF
    // Por ahora simulamos la descarga
    this.simularDescargaPDF(contenido, `ticket_${turno.codigo}.pdf`)

    return turno
  },

  simularDescargaPDF(contenido, nombreArchivo) {
    // Simular la descarga del PDF
    const blob = new Blob([contenido], { type: 'application/pdf' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = nombreArchivo
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(url)

    console.log(`PDF descargado: ${nombreArchivo}`)
  },
}
