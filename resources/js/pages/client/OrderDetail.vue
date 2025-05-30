<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Package, Truck } from 'lucide-vue-next';

defineProps<{
    order: {
        id: number;
        order_number: string;
        status: string;
        subtotal: number;
        tax: number;
        total: number;
        created_at: string;
        updated_at: string;
        items: Array<{
            id: number;
            product_name: string;
            quantity: number;
            price: number;
            total: number;
        }>;
        shipping_address: {
            address: string;
            city: string;
            state: string;
            postal_code: string;
            country: string;
        };
    };
}>();

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

const getStatusColor = (status) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusIcon = (status) => {
    switch (status.toLowerCase()) {
        case 'pending':
            return Package;
        case 'processing':
        case 'completed':
            return Truck;
        default:
            return Package;
    }
};
</script>

<template>
    <Head :title="`Order #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('client.orders')" class="mr-4">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Order #{{ order.order_number }}</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Order Status -->
                        <div class="mb-8 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium">Order Status</h3>
                                <div class="mt-2 flex items-center">
                                    <Badge :class="getStatusColor(order.status)" class="px-3 py-1 text-sm">
                                        <component :is="getStatusIcon(order.status)" class="mr-2 h-4 w-4" />
                                        {{ order.status }}
                                    </Badge>
                                    <span class="ml-4 text-sm text-muted-foreground">
                                        Placed on {{ formatDate(order.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <Button asChild variant="outline">
                                <Link :href="route('client.orders')">
                                    View All Orders
                                </Link>
                            </Button>
                        </div>

                        <Separator class="my-6" />

                        <!-- Order Items -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-medium">Order Items</h3>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Product</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Quantity</TableHead>
                                        <TableHead class="text-right">Total</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in order.items" :key="item.id">
                                        <TableCell class="font-medium">{{ item.product_name }}</TableCell>
                                        <TableCell>${{ item.price.toFixed(2) }}</TableCell>
                                        <TableCell>{{ item.quantity }}</TableCell>
                                        <TableCell class="text-right">${{ item.total.toFixed(2) }}</TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <!-- Shipping Address -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Shipping Address</h3>
                                <div class="rounded-md border p-4">
                                    <p>{{ order.shipping_address.address }}</p>
                                    <p>{{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}</p>
                                    <p>{{ order.shipping_address.country }}</p>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div>
                                <h3 class="mb-4 text-lg font-medium">Order Summary</h3>
                                <div class="rounded-md border p-4">
                                    <div class="flex justify-between py-2">
                                        <span>Subtotal</span>
                                        <span>${{ order.subtotal.toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span>Tax</span>
                                        <span>${{ order.tax.toFixed(2) }}</span>
                                    </div>
                                    <Separator class="my-2" />
                                    <div class="flex justify-between py-2 font-medium">
                                        <span>Total</span>
                                        <span>${{ order.total.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
