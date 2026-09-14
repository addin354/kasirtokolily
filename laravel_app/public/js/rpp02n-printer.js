/**
 * RPP02N & ESC/POS 58mm Web Thermal Printer Controller
 * Web Bluetooth & WebUSB Direct Printing Library for Kasir Toko Lily
 */
(function (window) {
    'use strict';

    class RPP02NPrinter {
        constructor() {
            this.btDevice = null;
            this.btServer = null;
            this.btCharacteristic = null;

            this.usbDevice = null;
            this.usbInterface = null;
            this.usbEndpoint = null;

            this.connectionType = null; // 'bluetooth' | 'usb'
        }

        /**
         * Check Web Bluetooth support
         */
        isBluetoothSupported() {
            return typeof navigator !== 'undefined' && 'bluetooth' in navigator;
        }

        /**
         * Check WebUSB support
         */
        isUSBSupported() {
            return typeof navigator !== 'undefined' && 'usb' in navigator;
        }

        /**
         * Connect to RPP02N via Web Bluetooth
         */
        async connectBluetooth() {
            if (!this.isBluetoothSupported()) {
                throw new Error('Browser ini tidak mendukung Web Bluetooth. Gunakan Google Chrome / MS Edge.');
            }

            const optionalServices = [
                '000018f0-0000-1000-8000-00805f9b34fb',
                '0000ff00-0000-1000-8000-00805f9b34fb',
                '0000e781-0000-1000-8000-00805f9b34fb',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
                '49535343-fe7d-4ae5-8fa9-9fafd205e455',
                '00001101-0000-1000-8000-00805f9b34fb'
            ];

            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: optionalServices
            });

            const server = await device.gatt.connect();
            let targetChar = null;
            const services = await server.getPrimaryServices();

            for (const service of services) {
                try {
                    const chars = await service.getCharacteristics();
                    for (const c of chars) {
                        if (c.properties.write || c.properties.writeWithoutResponse) {
                            targetChar = c;
                            break;
                        }
                    }
                } catch (e) {
                    console.warn('Gagal membaca karakteristik service:', e);
                }
                if (targetChar) break;
            }

            if (!targetChar) {
                throw new Error('Karakteristik pencetakan Bluetooth RPP02N tidak ditemukan.');
            }

            this.btDevice = device;
            this.btServer = server;
            this.btCharacteristic = targetChar;
            this.connectionType = 'bluetooth';

            localStorage.setItem('rpp02n_printer_connected', 'bluetooth');
            localStorage.setItem('rpp02n_printer_name', device.name || 'RPP02N Bluetooth Printer');

            return device.name || 'RPP02N Bluetooth Printer';
        }

        /**
         * Connect to RPP02N via WebUSB
         */
        async connectUSB() {
            if (!this.isUSBSupported()) {
                throw new Error('Browser ini tidak mendukung WebUSB. Gunakan Google Chrome / MS Edge.');
            }

            const device = await navigator.usb.requestDevice({ filters: [] });
            await device.open();
            if (device.configuration === null) {
                await device.selectConfiguration(1);
            }

            let interfaceNumber = 0;
            let endpointOut = null;

            for (const iface of device.configuration.interfaces) {
                for (const alt of iface.alternates) {
                    for (const ep of alt.endpoints) {
                        if (ep.direction === 'out') {
                            interfaceNumber = iface.interfaceNumber;
                            endpointOut = ep.endpointNumber;
                            break;
                        }
                    }
                }
            }

            if (!endpointOut) {
                throw new Error('Endpoint Output USB Printer RPP02N tidak ditemukan.');
            }

            await device.claimInterface(interfaceNumber);

            this.usbDevice = device;
            this.usbInterface = interfaceNumber;
            this.usbEndpoint = endpointOut;
            this.connectionType = 'usb';

            localStorage.setItem('rpp02n_printer_connected', 'usb');
            localStorage.setItem('rpp02n_printer_name', device.productName || 'RPP02N USB Printer');

            return device.productName || 'RPP02N USB Printer';
        }

        /**
         * Check if printer is active / connected
         */
        isConnected() {
            if (this.connectionType === 'bluetooth') {
                return !!(this.btDevice && this.btServer && this.btServer.connected && this.btCharacteristic);
            }
            if (this.connectionType === 'usb') {
                return !!(this.usbDevice && this.usbDevice.opened);
            }
            return false;
        }

        /**
         * Disconnect printer
         */
        disconnect() {
            if (this.btDevice && this.btDevice.gatt.connected) {
                this.btDevice.gatt.disconnect();
            }
            if (this.usbDevice && this.usbDevice.opened) {
                this.usbDevice.close();
            }
            this.btDevice = null;
            this.btServer = null;
            this.btCharacteristic = null;
            this.usbDevice = null;
            this.connectionType = null;

            localStorage.removeItem('rpp02n_printer_connected');
            localStorage.removeItem('rpp02n_printer_name');
        }

        /**
         * Send Uint8Array raw ESC/POS bytes to printer
         */
        async sendRawBytes(bytes) {
            if (!this.isConnected()) {
                throw new Error('Printer RPP02N belum terhubung.');
            }

            if (this.connectionType === 'bluetooth') {
                const chunkSize = 20; // Safe chunk size for BLE
                for (let i = 0; i < bytes.length; i += chunkSize) {
                    const chunk = bytes.slice(i, i + chunkSize);
                    if (this.btCharacteristic.properties.writeWithoutResponse) {
                        await this.btCharacteristic.writeValueWithoutResponse(chunk);
                    } else {
                        await this.btCharacteristic.writeValue(chunk);
                    }
                    await new Promise(r => setTimeout(r, 15));
                }
            } else if (this.connectionType === 'usb') {
                await this.usbDevice.transferOut(this.usbEndpoint, bytes);
            }
        }

        /**
         * Helper format line 32 chars max (58mm printer)
         */
        formatLine(left, right, maxLen = 32) {
            left = String(left || '');
            right = String(right || '');
            let spaces = maxLen - left.length - right.length;
            if (spaces < 1) {
                let availableLeft = maxLen - right.length - 1;
                if (availableLeft > 0) {
                    left = left.substring(0, availableLeft);
                    spaces = 1;
                } else {
                    spaces = 1;
                }
            }
            return left + ' '.repeat(spaces) + right;
        }

        /**
         * Build receipt ESC/POS binary data from receipt object
         */
        buildReceiptBytes(data) {
            const encoder = new TextEncoder();
            const buffer = [];

            const write = (str) => {
                const bytes = encoder.encode(str);
                for (let b of bytes) buffer.push(b);
            };

            const writeCmd = (cmdArray) => {
                for (let b of cmdArray) buffer.push(b);
            };

            // ESC @ Reset
            writeCmd([0x1B, 0x40]);
            // Select Code Table WPC1252
            writeCmd([0x1B, 0x74, 0x10]);

            // HEADER (Centered)
            writeCmd([0x1B, 0x61, 0x01]); // Align center
            writeCmd([0x1D, 0x21, 0x11]); // Double height & width for shop name
            write(data.toko_nama || 'LILY SEMBAKO');
            write('\n');

            writeCmd([0x1D, 0x21, 0x00]); // Normal text
            writeCmd([0x1B, 0x45, 0x00]); // Bold off
            if (data.toko_alamat) write(data.toko_alamat + '\n');
            if (data.toko_hp) write('HP: ' + data.toko_hp + '\n');
            write('================================\n');

            // INFO (Left align)
            writeCmd([0x1B, 0x61, 0x00]);
            write('No. Transaksi : ' + (data.no_transaksi || '-') + '\n');
            write('Tanggal       : ' + (data.tanggal || '-') + '\n');
            write('Kasir         : ' + (data.kasir || '-') + '\n');
            write('Pelanggan     : ' + (data.pelanggan || 'Umum') + '\n');
            write('Total Item    : ' + (data.total_item || '0') + '\n');
            write('--------------------------------\n');

            // ITEMS
            if (Array.isArray(data.items)) {
                for (const item of data.items) {
                    write(item.nama + '\n');
                    const qtyPrice = (item.qty || '1') + ' x ' + (item.harga || '0');
                    const line = this.formatLine(' ' + qtyPrice, 'Rp ' + (item.subtotal || '0'), 32);
                    write(line + '\n');
                    if (item.jenis) {
                        write(' (' + item.jenis + ')\n');
                    }
                }
            }
            write('--------------------------------\n');

            // TOTALS (Bold)
            if (data.ongkir && data.ongkir !== '0') {
                write(this.formatLine('ONGKIR', 'Rp ' + data.ongkir, 32) + '\n');
            }
            writeCmd([0x1B, 0x45, 0x01]); // Bold on
            write(this.formatLine('TOTAL', 'Rp ' + (data.total || '0'), 32) + '\n');
            writeCmd([0x1B, 0x45, 0x00]); // Bold off

            write(this.formatLine('BAYAR', 'Rp ' + (data.bayar || '0'), 32) + '\n');
            write(this.formatLine('KEMBALIAN', 'Rp ' + (data.kembalian || '0'), 32) + '\n');
            if (data.metode_pembayaran) {
                write(this.formatLine('METODE PMB', data.metode_pembayaran, 32) + '\n');
            }
            if (data.referensi) {
                write(this.formatLine('REFERENSI', data.referensi, 32) + '\n');
            }
            write('================================\n');

            // FOOTER (Centered)
            writeCmd([0x1B, 0x61, 0x01]); // Align center
            write('Barang yang sudah dibeli tidak\n');
            write('dapat ditukar/dikembalikan\n');
            write('kecuali ada kesalahan transaksi.\n\n');
            writeCmd([0x1B, 0x45, 0x01]);
            write('TERIMA KASIH\n');
            write('Atas Kunjungan Anda\n');
            writeCmd([0x1B, 0x45, 0x00]);
            write('================================\n\n\n\n');

            // Feed & Partial Cut
            writeCmd([0x1D, 0x56, 0x41, 0x00]);

            return new Uint8Array(buffer);
        }

        /**
         * Print receipt directly
         */
        async printReceipt(receiptData) {
            const bytes = this.buildReceiptBytes(receiptData);
            await this.sendRawBytes(bytes);
        }
    }

    // Attach to global window
    window.RPP02NPrinter = new RPP02NPrinter();

})(window);
