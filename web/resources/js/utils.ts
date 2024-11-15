export async function sha256(str: string) {
    // Get the string as arraybuffer.
    const buffer = new TextEncoder().encode(str)
    return crypto.subtle.digest("SHA-256", buffer).then(function (hash) {
        return buf2hex(hash);
    })
}

function buf2hex(buffer: ArrayBuffer) {
    return [...new Uint8Array(buffer)]
        .map(x => x.toString(16).padStart(2, '0'))
        .join('');
}
