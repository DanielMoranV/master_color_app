<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ShoppingCart, Package, MapPin, User } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    auth: {
        user: {
            name: string;
            email: string;
        };
    };
}>();
</script>

<template>
    <Head title="Client Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Client Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="mb-6 text-2xl font-bold">Welcome back, {{ auth.user.name }}!</h1>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <!-- Shopping Cart Card -->
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardTitle class="text-lg">Shopping Cart</CardTitle>
                                    <CardDescription>Continue shopping</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ShoppingCart class="h-12 w-12 text-primary" />
                                </CardContent>
                                <CardFooter>
                                    <Button asChild class="w-full">
                                        <Link :href="route('client.cart')">View Cart</Link>
                                    </Button>
                                </CardFooter>
                            </Card>

                            <!-- Orders Card -->
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardTitle class="text-lg">My Orders</CardTitle>
                                    <CardDescription>Track your orders</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <Package class="h-12 w-12 text-primary" />
                                </CardContent>
                                <CardFooter>
                                    <Button asChild class="w-full">
                                        <Link :href="route('client.orders')">View Orders</Link>
                                    </Button>
                                </CardFooter>
                            </Card>

                            <!-- Addresses Card -->
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardTitle class="text-lg">My Addresses</CardTitle>
                                    <CardDescription>Manage delivery addresses</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <MapPin class="h-12 w-12 text-primary" />
                                </CardContent>
                                <CardFooter>
                                    <Button asChild class="w-full">
                                        <Link :href="route('client.addresses')">Manage Addresses</Link>
                                    </Button>
                                </CardFooter>
                            </Card>

                            <!-- Profile Card -->
                            <Card>
                                <CardHeader class="pb-2">
                                    <CardTitle class="text-lg">My Profile</CardTitle>
                                    <CardDescription>Update your information</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <User class="h-12 w-12 text-primary" />
                                </CardContent>
                                <CardFooter>
                                    <Button asChild class="w-full">
                                        <Link :href="route('client.profile')">Edit Profile</Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        </div>

                        <div class="mt-8">
                            <h2 class="mb-4 text-xl font-semibold">Recent Orders</h2>
                            <p class="text-muted-foreground">Your recent order history will appear here.</p>
                            <Button asChild class="mt-4">
                                <Link :href="route('store')">Continue Shopping</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
