/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Filament-Grade Multi-Media Uploader (FilePond)
 *
 * Uses official FilePond (`react-filepond` + `filepond-plugin-image-preview` +
 * `filepond-plugin-image-crop` + `filepond-plugin-image-exif-orientation` +
 * `filepond-plugin-file-validate-type` + `filepond-plugin-file-validate-size`)
 * paired with Spatie Laravel MediaLibrary for multi-image product galleries
 * and 1:1 square cropped avatars up to 8MB.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { FilePond, registerPlugin } from 'react-filepond';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import { ImagePlus, Trash2, CheckCircle2 } from 'lucide-react';

import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

registerPlugin(
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginImageCrop,
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
);

export interface MultiMediaUploaderProps {
    name?: string;
    allowMultiple?: boolean;
    maxFiles?: number;
    maxFileSize?: string;
    cropAspectRatio?: string;
    initialGalleryUrls?: string[];
    uploadEndpoint?: string;
    deleteEndpoint?: string;
    csrfToken?: string;
    onUploadedPathsChange?: (paths: string[]) => void;
}

export function MultiMediaUploader({
    name = 'uploaded_tmp_files[]',
    allowMultiple = true,
    maxFiles = 8,
    maxFileSize = '8MB',
    cropAspectRatio,
    initialGalleryUrls = [],
    uploadEndpoint = '/uploads/tmp',
    deleteEndpoint = '/uploads/tmp',
    csrfToken,
    onUploadedPathsChange,
}: MultiMediaUploaderProps) {
    const [files, setFiles] = useState<unknown[]>([]);
    const [uploadedPaths, setUploadedPaths] = useState<string[]>([]);
    const [existingUrls, setExistingUrls] = useState<string[]>(initialGalleryUrls);

    const resolvedCsrf =
        csrfToken ||
        (typeof document !== 'undefined'
            ? document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
            : '');

    const addPath = (path: string) => {
        setUploadedPaths((prev) => {
            const next = prev.includes(path) ? prev : [...prev, path];
            onUploadedPathsChange?.(next);
            return next;
        });
    };

    const removePath = (path: string) => {
        setUploadedPaths((prev) => {
            const next = prev.filter((item) => item !== path);
            onUploadedPathsChange?.(next);
            return next;
        });
    };

    return (
        <div className="space-y-3" data-v2-filepond-uploader="true">
            {uploadedPaths.map((p) => (
                <input key={p} type="hidden" name={name} value={p} />
            ))}

            {existingUrls.length > 0 && (
                <div className="space-y-1.5">
                    <div className="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400">
                        <span className="inline-flex items-center gap-1.5">
                            <CheckCircle2 className="h-3.5 w-3.5 text-emerald-500" />
                            <span>Galeri Tersimpan ({existingUrls.length} foto)</span>
                        </span>
                    </div>
                    <div className="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                        {existingUrls.map((url, idx) => (
                            <div
                                key={`${url}-${idx}`}
                                className="group relative aspect-square rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800"
                            >
                                <img
                                    src={url}
                                    alt={`Foto barang ${idx + 1}`}
                                    className="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    onClick={() =>
                                        setExistingUrls((prev) => prev.filter((_, i) => i !== idx))
                                    }
                                    className="absolute top-1.5 right-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900/75 text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                    title="Sembunyikan dari pratinjau"
                                >
                                    <Trash2 className="h-3 w-3" />
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="rounded-2xl border border-dashed border-blue-500/40 dark:border-blue-400/30 bg-blue-50/30 dark:bg-slate-900/60 p-2">
                <div className="mb-1.5 flex items-center justify-between px-2 pt-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                    <span className="inline-flex items-center gap-1.5">
                        <ImagePlus className="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                        <span>Multi-Upload & Pratinjau Thumbnail HD (Maks {maxFiles} file • {maxFileSize})</span>
                    </span>
                </div>
                <FilePond
                    files={files as never[]}
                    onupdatefiles={setFiles}
                    allowMultiple={allowMultiple}
                    allowReorder={allowMultiple}
                    maxFiles={maxFiles}
                    maxFileSize={maxFileSize}
                    acceptedFileTypes={['image/jpeg', 'image/png', 'image/webp', 'image/jpg']}
                    imageCropAspectRatio={cropAspectRatio}
                    name="file"
                    credits={false}
                    labelIdle='Seret & Lepas foto barang di sini atau <span class="filepond--label-action">Pilih File</span>'
                    server={{
                        process: (
                            _fieldName,
                            file,
                            _metadata,
                            load,
                            error,
                            progress,
                            abort,
                        ) => {
                            const formData = new FormData();
                            formData.append('file', file, file.name);

                            const xhr = new XMLHttpRequest();
                            xhr.open('POST', uploadEndpoint, true);
                            xhr.setRequestHeader('X-CSRF-TOKEN', resolvedCsrf);
                            xhr.setRequestHeader('Accept', 'application/json');

                            xhr.upload.onprogress = (e) => {
                                progress(e.lengthComputable, e.loaded, e.total);
                            };

                            xhr.onload = () => {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    try {
                                        const parsed = JSON.parse(xhr.responseText) as {
                                            path?: string;
                                        };
                                        const storedPath = parsed.path ?? '';
                                        if (storedPath) {
                                            addPath(storedPath);
                                            load(storedPath);
                                            return;
                                        }
                                    } catch {
                                        // Fallback below
                                    }
                                }
                                error('Gagal mengunggah gambar sementara');
                            };

                            xhr.onerror = () => error('Kesalahan jaringan saat unggah gambar');
                            xhr.send(formData);

                            return {
                                abort: () => {
                                    xhr.abort();
                                    abort();
                                },
                            };
                        },
                        revert: (uniqueFileId, load, error) => {
                            removePath(uniqueFileId);
                            fetch(deleteEndpoint, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': resolvedCsrf,
                                    Accept: 'application/json',
                                },
                                body: JSON.stringify({ path: uniqueFileId }),
                            })
                                .then(() => load())
                                .catch(() => error('Gagal menghapus file sementara'));
                        },
                    }}
                />
            </div>
        </div>
    );
}
