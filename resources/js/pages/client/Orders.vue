<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Eye, ShoppingBag } from 'lucide-vue-next';

defineProps<{
    orders: Array<{
        id: number;
        order_number: string;
        status: string;
        total: number;
        created_at: string;
    }>;
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
</script>

<template>
    <Head title="My Orders" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My Orders</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="orders.length === 0" class="flex flex-col items-center justify-center py-12">
                            <ShoppingBag class="mb-4 h-16 w-16 text-muted-foreground" />
                            <h3 class="mb-2 text-xl font-medium">No orders yet</h3>
                            <p class="mb-6 text-muted-foreground">Start shopping to see your orders here</p>
                            <Button asChild>
                                <Link :href="route('store')">Browse Products</Link>
                            </Button>
                        </div>

                        <div v-else>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Order #</TableHead>
                                        <TableHead>Date</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Total</TableHead>
                                        <TableHead class="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="order in orders" :key="order.id">
                                        <TableCell class="font-medium">{{ order.order_number }}</TableCell>
                                        <TableCell>{{ formatDate(order.created_at) }}</TableCell>
                                        <TableCell>
                                            <Badge :class="getStatusColor(order.status)">
                                                {{ order.status }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>${{ order.total.toFixed(2) }}</TableCell>
                                        <TableCell class="text-right">
                                            <Button asChild variant="ghost" size="sm">
                                                <Link :href="route('client.orders.show', order.id)">
                                                    <Eye class="mr-2 h-4 w-4" />
                                                    View
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
