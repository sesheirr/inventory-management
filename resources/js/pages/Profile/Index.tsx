/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Profile Studio (React 19 + Inertia v2)
 *
 * Features role badge stacked below name, "Ubah" & "Simpan" labels,
 * `<SmartPhoneInput />` E.164 international phone detection, and 1:1 FilePond
 * square avatar crop studio (max 8MB).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { SmartPhoneInput } from '../../components/ui/SmartPhoneInput';
import { MultiMediaUploader } from '../../components/upload/MultiMediaUploader';
import { Edit3, Save } from 'lucide-react';

export interface ProfileIndexProps {
    user: {
        id: number;
        name: string;
        email: string;
        phone?: string | null;
        role_label?: string;
        avatar_url?: string | null;
    };
    csrfToken?: string;
}

export default function ProfileIndex({ user, csrfToken = '' }: ProfileIndexProps) {
    const [phone, setPhone] = useState<string>(user?.phone ?? '+62');
    const [editing, setEditing] = useState<boolean>(false);

    return (
        <AppLayout
            title="Profil Pegawai"
            subtitle="Kelola identitas akun, nomor telepon internasional E.164, dan foto profil 1:1."
        >
            <div className="max-w-3xl rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <div className="flex flex-col items-start gap-1">
                        <h2 className="text-lg font-extrabold text-slate-900 dark:text-white">
                            {user?.name}
                        </h2>
                        <span className="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-950/70 px-3 py-0.5 text-xs font-bold text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                            {user?.role_label ?? 'Pegawai Diskominfo'}
                        </span>
                    </div>

                    <button
                        type="button"
                        onClick={() => setEditing((v) => !v)}
                        className="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer"
                    >
                        <Edit3 className="h-3.5 w-3.5" />
                        <span>Ubah</span>
                    </button>
                </div>

                <form
                    action="/profile"
                    method="POST"
                    encType="multipart/form-data"
                    className="space-y-4 pt-2 border-t border-slate-100 dark:border-slate-800"
                >
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            name="name"
                            defaultValue={user?.name}
                            disabled={!editing}
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none"
                        />
                    </div>

                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Telepon (Smart International Detection E.164)
                        </label>
                        <SmartPhoneInput value={phone} onChange={setPhone} />
                    </div>

                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Foto Profil (1:1 Square Crop • Maks 8MB)
                        </label>
                        <MultiMediaUploader
                            allowMultiple={false}
                            maxFiles={1}
                            maxFileSize="8MB"
                            cropAspectRatio="1:1"
                            csrfToken={csrfToken}
                        />
                    </div>

                    <div className="flex justify-end">
                        <button
                            type="submit"
                            className="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-5 py-2.5 text-xs font-bold text-white shadow-md cursor-pointer"
                        >
                            <Save className="h-4 w-4" />
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
