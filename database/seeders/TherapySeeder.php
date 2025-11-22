<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Therapy;
use App\Models\TherapyPage;
use App\Models\User;
use Illuminate\Support\Str;

class TherapySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure an author user exists
        $author = User::where('email', 'seed@passiflor.test')->first();
        if (! $author) {
            $author = User::create([
                'name' => 'Seeder Author',
                'email' => 'seed@passiflor.test',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
        }

        // Create therapy
        $therapy = Therapy::create([
            'slug' => 'entrenamiento-para-ir-al-bano',
            'title' => 'Entrenamiento para ir al baño',
            'short_description' => 'Guía breve para iniciar el entrenamiento para ir al baño (poopy training).',
            'cover_image' => null,
            'duration_minutes' => null,
            'age_from' => 24,
            'age_to' => 48,
            'assigned_patient_id' => null,
            'author_id' => $author->id,
            'published' => true,
        ]);

        $pages = [];

        // Hero page
        $pages[] = [
            'type' => 'hero',
            'number' => null,
            'title' => 'Entrenamiento para ir al baño',
            'subtitle' => 'Planificación terapéutica',
            'body' => "Emma Egas Lara\n593979136467\nverordonezp@passiflor.org",
            'list_items' => null,
            'note' => null,
        ];

        // Page 1
        $pages[] = [
            'type' => 'step',
            'number' => 1,
            'title' => '¿Está lista para dejar el pañal?',
            'subtitle' => null,
            'body' => 'Sigue este test rápido* y descubre si es un buen momento para iniciar el poopy training.',
            'list_items' => null,
            'note' => '*Ideal para niñas/os de 2 años.',
        ];

        // Page 2
        $pages[] = [
            'type' => 'step',
            'number' => 2,
            'title' => 'Esperar el momento adecuado',
            'subtitle' => null,
            'body' => null,
            'list_items' => [
                'Empezar muy pronto puede generar frustración.',
                'Empezar con señales claras de preparación evita traumas.',
                'Cada niño/a tiene su ritmo.',
                'Observa, no apresures.',
            ],
            'note' => '*Ideal para niñas/os de 2 años.',
        ];

        // Page 3
        $pages[] = [
            'type' => 'step',
            'number' => 3,
            'title' => 'Señales físicas de preparación',
            'subtitle' => null,
            'body' => null,
            'list_items' => [
                'Permanece seca por 2+ horas seguidas.',
                'Hace popó a horarios previsibles.',
                'Se esconde o avisa antes de evacuar.',
            ],
            'note' => null,
        ];

        // Page 4
        $pages[] = [
            'type' => 'step',
            'number' => 4,
            'title' => 'Habilidades básicas necesarias',
            'subtitle' => 'Observa si muestra estas actitudes',
            'body' => null,
            'list_items' => [
                'Caminar y sentarse sola/o.',
                'Bajar sus pantalones con ayuda.',
                'Seguir instrucciones simples.',
                'Imitar a otros usando el baño.',
            ],
            'note' => null,
        ];

        // Page 5
        $pages[] = [
            'type' => 'step',
            'number' => 5,
            'title' => 'Interés emocional y disposición',
            'subtitle' => 'Observa si muestra estas actitudes:',
            'body' => null,
            'list_items' => [
                'Se interesa por el orinal o el baño.',
                'Le molesta el pañal sucio.',
                'Acepta elogios con alegría.',
                'Se siente segura probando rutinas nuevas.',
            ],
            'note' => null,
        ];

        // Page 6
        $pages[] = [
            'type' => 'step',
            'number' => 6,
            'title' => '¿Qué pasa en casa?',
            'subtitle' => 'El entorno también influye:',
            'body' => null,
            'list_items' => [
                'No hay grandes cambios recientes(mudanzas, hermanos, etc.).',
                'Tienes tiempo y paciencia para acompañar.',
                'Hay un orinal visible y accesible.',
            ],
            'note' => null,
        ];

        // Page 7
        $pages[] = [
            'type' => 'step',
            'number' => 7,
            'title' => '¿Qué hacer con los resultados?',
            'subtitle' => null,
            'body' => null,
            'list_items' => [
                'Muchos Sí: Comienza con el entrenamiento suave y estructurado.',
                'Tienes tiempo y paciencia para acompañar.',
                'Hay un orinal visible y accesible.',
            ],
            'note' => null,
        ];

        // Page 8
        $pages[] = [
            'type' => 'step',
            'number' => 8,
            'title' => 'Señales normales en el proceso',
            'subtitle' => '(cosas que pueden pasar y no deben preocuparte):',
            'body' => null,
            'list_items' => [
                'Accidentes ocasionales.',
                'Retención del popó unos días.',
                'Hay un orinal visible y accesible.',
                'Cambios leves en el horario.',
            ],
            'note' => null,
        ];

        // Page 9
        $pages[] = [
            'type' => 'step',
            'number' => 9,
            'title' => 'Cuándo detener el entrenamiento',
            'subtitle' => 'Señales para pausar el proceso:',
            'body' => null,
            'list_items' => [
                'Llanto intenso o miedo al orinal.',
                'Estreñimiento con dolor.',
                'Rechazo firme y sostenido.',
                'Retrocesos en otras áreas (sueño, apetito, juego).',
            ],
            'note' => null,
        ];

        // Page 10
        $pages[] = [
            'type' => 'step',
            'number' => 10,
            'title' => 'Tips extra del enfoque respetuoso',
            'subtitle' => null,
            'body' => null,
            'list_items' => [
                'Nunca forzar.',
                'Celebrar los pequeños logros.',
                'Ser constante pero flexible.',
                'Anticipar lo sensorial (olor, papel, espacio).',
            ],
            'note' => '*Basado en Autistic Logistics y la AAP.',
        ];

        // Page 11
        $pages[] = [
            'type' => 'step',
            'number' => 11,
            'title' => 'Recursos visuales recomendados',
            'subtitle' => 'Usa pictogramas para ayudarle a anticipar el paso a paso:',
            'body' => null,
            'list_items' => [
                'Sentarse.',
                'Hacer popó.',
                'Limpiarse.',
                'Vaciar.',
                'Lavarse las manos.',
                'Celebrar con una estrellita.',
            ],
            'note' => null,
        ];

        // Page 12
        $pages[] = [
            'type' => 'step',
            'number' => 12,
            'title' => 'Cada proceso es único.',
            'subtitle' => null,
            'body' => 'Confía en tu peque, observa sin prisa y honra el ritmo de su cuerpo y emociones.',
            'list_items' => null,
            'note' => null,
        ];

        foreach ($pages as $i => $p) {
            $therapy->pages()->create(array_merge([
                'position' => $i,
            ], $p));
        }
    }
}
