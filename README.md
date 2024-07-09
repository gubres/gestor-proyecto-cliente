# AppGestor

## La mejor gestión para tus proyectos y clientes.

**AppGestor** es una web app desarrollada en Symfony que ofrece una solución avanzada para la gestión de proyectos, optimizada para equipos que buscan maximizar su productividad y mejorar la colaboración interna.

## Características Principales

- **Creación y Planificación de Tareas**: Los usuarios pueden crear y planificar tareas con facilidad, configurando fechas de inicio y de finalización.
- **Asignación de Tareas**: Asigna tareas a miembros del equipo y establece prioridades para asegurar que los proyectos se completen a tiempo.
- **Seguimiento en Tiempo Real**: Permite el seguimiento del progreso de cada tarea en tiempo real, facilitando la identificación rápida de cuellos de botella.
- **Redistribución de Recursos**: Facilita la redistribución de recursos cuando sea necesario para mantener el flujo del proyecto.

## Tecnologías Utilizadas

- **Framework**: Symfony
- **Lenguaje**: PHP
- **Base de Datos**: MySQL / PostgreSQL
- **Frontend**: HTML, CSS, JavaScript
- **Gestión de Dependencias**: Composer

## Instalación

Sigue estos pasos para instalar y configurar AppGestor en tu entorno local:

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/tu-usuario/appgestor.git
   cd appgestor
   
2. Instalar dependencias:

   composer install

3. Configurar el entorno:
Copia el archivo .env y configura tu entorno:

   cp .env .env.local

   Edita el archivo .env.local para configurar tu base de datos y otros parámetros.

4. Crear la base de datos:

   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate

5. Iniciar el servidor de desarrollo:

    symfony server:start

    Acceder a la aplicación:
    Abre tu navegador y ve a http://127.0.0.1:8000.

Uso

    Crear Proyectos: Comienza creando un nuevo proyecto desde el panel de control.
    Asignar Tareas: Añade tareas al proyecto y asígnalas a los miembros del equipo.
    Seguimiento de Progreso: Monitorea el progreso en tiempo real desde el tablero de control.

Licencia

Este proyecto está licenciado bajo la MIT License.
