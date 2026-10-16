/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Rooms Directory (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Building2, UserCheck } from 'lucide-react';

export interface RoomItem {
    id: number;
    name: string;
    code?: string | null;
    penanggung_jawab?: string | null;
    products_count?: number;
}

export interface RoomsIndexProps {
    rooms?: RoomItem[];
}

export default function RoomsIndex({ rooms = [] }: RoomsIndexProps) {
    return (
        <AppLayout
            title="Direktori Ruangan & Bidang Kerja"
            subtitle="Pemetaan lokasi fisik aset TIK beserta penanggung jawab ruangan di Diskominfo Garut."
        >
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {rooms.map((room) => (
                    <a
                        key={room.id}
                        href={`/rooms/${room.id}`}
                        className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-2xs hover:border-blue-500/50 transition-all space-y-2"
                    >
                        <div className="flex items-center justify-between">
                            <Building2 className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <span className="font-mono text-xs font-bold text-slate-400">
                                {room.code ?? `RM-${room.id}`}
                            </span>
                        </div>
                        <div className="text-base font-bold text-slate-900 dark:text-white">
                            {room.name}
                        </div>
                        <div className="flex items-center gap-1.5 text-xs text-slate-500">
                            <UserCheck className="h-3.5 w-3.5" />
                            <span>PJ: {room.penanggung_jawab ?? 'Belum Ditetapkan'}</span>
                        </div>
                    </a>
                ))}
            </div>
        </AppLayout>
    );
}
