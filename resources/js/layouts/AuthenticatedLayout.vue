<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { UserCircle2 } from 'lucide-vue-next'

const props = defineProps({
    title: String,
    description: String,
})
</script>

<template>
    <div class="min-h-screen bg-background">
        <nav class="bg-background border-b">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <Link href="/" class="text-2xl font-bold">
                        Master Color
                    </Link>
                    
                    <div class="flex items-center gap-4">
                        <Link href="/dashboard" class="text-sm">Dashboard</Link>
                        <Link href="/profile" class="text-sm">Perfil</Link>
                        <Button variant="ghost" as="a" href="{{ route('logout') }}" method="post">
                            <UserCircle2 class="w-4 h-4 mr-2" />
                            Cerrar Sesión
                        </Button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container mx-auto px-4 py-8">
            <div v-if="title || description" class="mb-8">
                <h1 v-if="title" class="text-3xl font-bold mb-2">{{ title }}</h1>
                <p v-if="description" class="text-muted-foreground">{{ description }}</p>
            </div>
            <slot />
        </main>
    </div>
</template>
