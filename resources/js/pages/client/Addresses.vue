<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { MapPin, Plus, Pencil, Trash2 } from 'lucide-vue-next';

defineProps<{
    addresses: Array<{
        id: number;
        address: string;
        city: string;
        state: string;
        postal_code: string;
        country: string;
        is_default: boolean;
    }>;
}>();

const showAddressForm = ref(false);
const editingAddress = ref(null);

const form = useForm({
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: 'Peru',
    is_default: false,
});

const resetForm = () => {
    form.reset();
    editingAddress.value = null;
};

const openEditForm = (address) => {
    form.address = address.address;
    form.city = address.city;
    form.state = address.state;
    form.postal_code = address.postal_code;
    form.country = address.country;
    form.is_default = address.is_default;
    editingAddress.value = address.id;
    showAddressForm.value = true;
};

const submitForm = () => {
    if (editingAddress.value) {
        form.put(route('client.addresses.update', editingAddress.value), {
            onSuccess: () => {
                showAddressForm.value = false;
                resetForm();
            },
        });
    } else {
        form.post(route('client.addresses.store'), {
            onSuccess: () => {
                showAddressForm.value = false;
                resetForm();
            },
        });
    }
};

const deleteAddress = (id) => {
    if (confirm('Are you sure you want to delete this address?')) {
        useForm({}).delete(route('client.addresses.destroy', id));
    }
};
</script>

<template>
    <Head title="My Addresses" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">My Addresses</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-medium">Saved Addresses</h3>
                            <Button @click="showAddressForm = true; resetForm()">
                                <Plus class="mr-2 h-4 w-4" />
                                Add New Address
                            </Button>
                        </div>

                        <div v-if="addresses.length === 0" class="flex flex-col items-center justify-center py-12">
                            <MapPin class="mb-4 h-16 w-16 text-muted-foreground" />
                            <h3 class="mb-2 text-xl font-medium">No addresses yet</h3>
                            <p class="mb-6 text-muted-foreground">Add an address to get started</p>
                            <Button @click="showAddressForm = true; resetForm()">
                                <Plus class="mr-2 h-4 w-4" />
                                Add New Address
                            </Button>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card v-for="address in addresses" :key="address.id" class="relative">
                                <CardHeader>
                                    <CardTitle class="flex items-center">
                                        <MapPin class="mr-2 h-5 w-5 text-primary" />
                                        {{ address.city }}
                                        <span v-if="address.is_default" class="ml-2 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">
                                            Default
                                        </span>
                                    </CardTitle>
                                    <CardDescription>{{ address.address }}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
                                    <p>{{ address.country }}</p>
                                </CardContent>
                                <CardFooter class="flex justify-end space-x-2">
                                    <Button variant="outline" size="sm" @click="openEditForm(address)">
                                        <Pencil class="mr-2 h-4 w-4" />
                                        Edit
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="deleteAddress(address.id)">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Delete
                                    </Button>
                                </CardFooter>
                            </Card>
                        </div>

                        <Dialog v-model:open="showAddressForm">
                            <DialogContent class="sm:max-w-[425px]">
                                <DialogHeader>
                                    <DialogTitle>{{ editingAddress ? 'Edit Address' : 'Add New Address' }}</DialogTitle>
                                    <DialogDescription>
                                        {{ editingAddress ? 'Update your delivery address details' : 'Enter your delivery address details' }}
                                    </DialogDescription>
                                </DialogHeader>

                                <form @submit.prevent="submitForm" class="mt-4 space-y-4">
                                    <div class="grid gap-2">
                                        <Label for="address">Street Address</Label>
                                        <Input id="address" v-model="form.address" required />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Label for="city">City</Label>
                                            <Input id="city" v-model="form.city" required />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="state">State/Province</Label>
                                            <Input id="state" v-model="form.state" required />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="grid gap-2">
                                            <Label for="postal_code">Postal Code</Label>
                                            <Input id="postal_code" v-model="form.postal_code" required />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="country">Country</Label>
                                            <Input id="country" v-model="form.country" required />
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_default"
                                            type="checkbox"
                                            v-model="form.is_default"
                                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                        <Label for="is_default" class="text-sm">Set as default address</Label>
                                    </div>

                                    <DialogFooter>
                                        <Button type="button" variant="outline" @click="showAddressForm = false">
                                            Cancel
                                        </Button>
                                        <Button type="submit" :disabled="form.processing">
                                            {{ editingAddress ? 'Update' : 'Save' }}
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
