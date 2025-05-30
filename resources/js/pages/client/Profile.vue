<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            type: string;
            type_document: string;
            identity_document: string;
            phone: string;
        };
    };
}>();

const form = useForm({
    name: props.auth.user.name,
    email: props.auth.user.email,
    type: props.auth.user.type,
    type_document: props.auth.user.type_document,
    identity_document: props.auth.user.identity_document,
    phone: props.auth.user.phone,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    form.put(route('client.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'password', 'password_confirmation');
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route('client.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Profile</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Update your account's profile information and email address.
                                </p>
                            </header>

                            <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                                <div>
                                    <Label for="name">Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    />
                                </div>

                                <div>
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                        autocomplete="username"
                                    />
                                </div>
                                
                                <div>
                                    <Label for="type">Client Type</Label>
                                    <Select v-model="form.type">
                                        <SelectTrigger class="mt-1 w-full">
                                            <SelectValue placeholder="Select client type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="persona">Individual</SelectItem>
                                            <SelectItem value="empresa">Business</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                
                                <div>
                                    <Label for="type_document">Document Type</Label>
                                    <Select v-model="form.type_document">
                                        <SelectTrigger class="mt-1 w-full">
                                            <SelectValue placeholder="Select document type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="DNI">DNI</SelectItem>
                                            <SelectItem value="RUC">RUC</SelectItem>
                                            <SelectItem value="CE">CE</SelectItem>
                                            <SelectItem value="PASAPORTE">Passport</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                
                                <div>
                                    <Label for="identity_document">Document Number</Label>
                                    <Input
                                        id="identity_document"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.identity_document"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <Label for="phone">Phone Number</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.phone"
                                        required
                                    />
                                </div>

                                <div class="flex items-center gap-4">
                                    <Button :disabled="form.processing">
                                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Save
                                    </Button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">Update Password</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    Ensure your account is using a long, random password to stay secure.
                                </p>
                            </header>

                            <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                                <div>
                                    <Label for="current_password">Current Password</Label>
                                    <Input
                                        id="current_password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="passwordForm.current_password"
                                        autocomplete="current-password"
                                    />
                                </div>

                                <div>
                                    <Label for="password">New Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="passwordForm.password"
                                        autocomplete="new-password"
                                    />
                                </div>

                                <div>
                                    <Label for="password_confirmation">Confirm Password</Label>
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="passwordForm.password_confirmation"
                                        autocomplete="new-password"
                                    />
                                </div>

                                <div class="flex items-center gap-4">
                                    <Button :disabled="passwordForm.processing">
                                        <LoaderCircle v-if="passwordForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Save
                                    </Button>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
