const nfcSerialElement = document.getElementById('nfc-serial') as HTMLDivElement;
const nfcSerialButton = document.getElementById('nfc-serial-btn') as HTMLButtonElement;
const nfcSerialStatus = document.getElementById('nfc-serial-status') as HTMLDivElement;

let selectedSerialPort: { usbVendorId: number, usbProductId: number } | null = null;

try {
    selectedSerialPort = JSON.parse(localStorage.getItem('serialPort') ?? 'null');
} catch (e) {
    console.error('Failed to parse stored serial port:', e);
}

function concatArrayBuffers(chunks: Uint8Array[]): Uint8Array {
    const result = new Uint8Array(chunks.reduce((a, c) => a + c.length, 0));
    let offset = 0;
    for (const chunk of chunks) {
        result.set(chunk, offset);
        offset += chunk.length;
    }
    return result;
}

class LineSplitter extends TransformStream<Uint8Array, Uint8Array> {
    protected _buffer: Uint8Array[] = [];

    constructor() {
        super({
            transform: (chunk, controller) => {
                let index;
                let rest = chunk;
                while ((index = rest.indexOf(0x0a)) !== -1) {
                    controller.enqueue(concatArrayBuffers([...this._buffer, rest.slice(0, index + 1)]));
                    rest = rest.slice(index + 1);
                    this._buffer = [];
                }

                if (rest.length > 0) {
                    this._buffer.push(rest);
                }
            },
            flush: (controller) => {
                if (this._buffer.length > 0) {
                    controller.enqueue(concatArrayBuffers(this._buffer));
                }
            }
        });
    }
}

const nfcHexRegex = /  tagId Hex: (.+)/;

async function connectSerialPort(port: SerialPort) {
    await port.open({baudRate: 115200});
    nfcSerialStatus.textContent = 'Connected to ' + port.getInfo().usbProductId + ' on ' + port.getInfo().usbVendorId;

    await port.readable.pipeThrough(new LineSplitter()).pipeTo(new WritableStream({
        write(lineRaw) {
            const str = new TextDecoder().decode(lineRaw);
            console.log(str);

            const match = nfcHexRegex.exec(str);
            if (match) {
                const tagIdHex = match[1];
                document.querySelectorAll('input[name="new_tag_id"]').forEach((input) => {
                    (input as HTMLInputElement).value = tagIdHex;
                });

                document.querySelectorAll('input[name="tag_id"]').forEach((input) => {
                    (input as HTMLInputElement).value = tagIdHex;
                });
            }
        }
    })).catch((e) => {
        console.error('Error:', e);
    });

    nfcSerialStatus.textContent = 'Disconnected';
}

nfcSerialButton.addEventListener('click', () => {
    navigator.serial.requestPort().then((port) => {
        localStorage.setItem('serialPort', JSON.stringify(port.getInfo()));
        connectSerialPort(port);
    });
});

if (selectedSerialPort) {
    navigator.serial.getPorts().then(r => {
        r.forEach(port => {
            // @ts-ignore
            if (port.getInfo().usbVendorId === selectedSerialPort.usbVendorId && port.getInfo().usbProductId === selectedSerialPort.usbProductId) {
                connectSerialPort(port);
            }
        });
    });
}
