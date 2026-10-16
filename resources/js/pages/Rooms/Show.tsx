/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Room Detail Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Building2, Package } from 'lucide-react';

export interface RoomShowProps {
    room: {
        id: number;
        name: string;
        code?: string | null;
        penanggung_jawab?: string | null;
    };
}

export default function RoomShow({ room }: RoomShowProps) {
    return (
        <AppLayout
            title={`Ruangan: ${room.name}`}
            subtitle={`Kode Ruangan: ${room.code ?? '-'} • Penanggung Jawab: ${room.penanggung_jawab ?? '-'}`}
        >
            <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Building2 className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h2 className="text-base font-extrabold text-slate-900 dark:text-white">
                            {room.name}
                        </h2>
                        <p className="text-xs text-slate-500">
                            Penanggung Jawab: {room.penanggung_jawab ?? 'Belum Ditetapkan'}
                        </p>
                    </div>
                </div>
                <span className="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 dark:text-blue-400">
                    <Package className="h-4 w-4" />
                    <span>Inventaris Ruangan</span>
                </span>
            </div>
        </AppLayout>
    );
}
