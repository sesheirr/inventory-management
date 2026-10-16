/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Smart International Phone Input (E.164)
 *
 * Automatically detects Indonesian (+62) and international country codes,
 * normalizes pasted numbers (08xx -> +628xx), and prevents horizontal UI overflow.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useMemo, useState } from 'react';

export interface CountryOption {
    code: string;
    dial: string;
    name: string;
    flag: string;
}

export const COUNTRY_DIAL_CODES: CountryOption[] = [
    { code: 'ID', dial: '+62', name: 'Indonesia', flag: '🇮🇩' },
    { code: 'MY', dial: '+60', name: 'Malaysia', flag: '🇲🇾' },
    { code: 'SG', dial: '+65', name: 'Singapore', flag: '🇸🇬' },
    { code: 'US', dial: '+1', name: 'United States', flag: '🇺🇸' },
    { code: 'GB', dial: '+44', name: 'United Kingdom', flag: '🇬🇧' },
    { code: 'AU', dial: '+61', name: 'Australia', flag: '🇦🇺' },
    { code: 'JP', dial: '+81', name: 'Japan', flag: '🇯🇵' },
    { code: 'SA', dial: '+966', name: 'Saudi Arabia', flag: '🇸🇦' },
];

export interface SmartPhoneInputProps {
    name?: string;
    value: string;
    onChange: (e164Value: string) => void;
    placeholder?: string;
    error?: string;
}

export function normalizePhoneToE164(raw: string, selectedDial = '+62'): string {
    const cleaned = raw.replace(/[^\d+]/g, '');
    if (!cleaned) return '';
    if (cleaned.startsWith('+')) return cleaned;
    if (cleaned.startsWith('0')) {
        return `${selectedDial}${cleaned.slice(1)}`;
    }
    if (cleaned.startsWith('62') && selectedDial === '+62') {
        return `+${cleaned}`;
    }
    return `${selectedDial}${cleaned}`;
}

export function SmartPhoneInput({
    name = 'phone',
    value,
    onChange,
    placeholder = '812-3456-7890',
    error,
}: SmartPhoneInputProps) {
    const detectedCountry = useMemo(() => {
        if (!value) return COUNTRY_DIAL_CODES[0];
        const match = [...COUNTRY_DIAL_CODES]
            .sort((a, b) => b.dial.length - a.dial.length)
            .find((c) => value.startsWith(c.dial));
        return match ?? COUNTRY_DIAL_CODES[0];
    }, [value]);

    const [dial, setDial] = useState<string>(detectedCountry.dial);

    const localDigits = useMemo(() => {
        if (!value) return '';
        if (value.startsWith(dial)) {
            return value.slice(dial.length);
        }
        return value.replace(/^\+\d{1,3}/, '');
    }, [value, dial]);

    const handleLocalInput = (inputVal: string) => {
        const nextE164 = normalizePhoneToE164(inputVal, dial);
        onChange(nextE164);
    };

    return (
        <div className="w-full max-w-full min-w-0">
            <input type="hidden" name={name} value={value} />
            <div className="flex w-full max-w-full min-w-0 items-stretch overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus-within:ring-2 focus-within:ring-blue-500/30 focus-within:border-blue-500 transition-all">
                <select
                    aria-label="Kode Negara"
                    value={dial}
                    onChange={(e) => {
                        const newDial = e.target.value;
                        setDial(newDial);
                        if (localDigits) {
                            onChange(`${newDial}${localDigits.replace(/^0+/, '')}`);
                        }
                    }}
                    className="shrink-0 bg-slate-50 dark:bg-slate-800/80 px-3 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 border-r border-slate-200 dark:border-slate-700 outline-none cursor-pointer"
                >
                    {COUNTRY_DIAL_CODES.map((c) => (
                        <option key={c.code} value={c.dial}>
                            {c.flag} {c.dial}
                        </option>
                    ))}
                </select>
                <input
                    type="tel"
                    inputMode="tel"
                    value={localDigits}
                    onChange={(e) => handleLocalInput(e.target.value)}
                    placeholder={placeholder}
                    className="flex-1 min-w-0 w-full bg-transparent px-3.5 py-2.5 text-sm font-medium text-slate-900 dark:text-slate-100 placeholder:text-slate-400 outline-none"
                />
            </div>
            {error && <p className="mt-1.5 text-xs font-medium text-rose-600 dark:text-rose-400">{error}</p>}
        </div>
    );
}
