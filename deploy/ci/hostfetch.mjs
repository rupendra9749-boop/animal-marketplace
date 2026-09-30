// Fetches a URL from the InfinityFree host, passing the host's "please enable JavaScript" browser check.
// The check is a small AES puzzle: decrypt c with key a and iv b (AES-128-CBC), put the result in the cookie __test.
//
//   node hostfetch.mjs <url>                     -> prints "HTTP <status>" then the body
//   node hostfetch.mjs <url> --expect-status 200 --expect-text "Application up"   -> exits 1 when they do not match
import crypto from 'node:crypto';

const UA = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36 AnimalMandiDeploy/1.0';
let cookie = '';

const hex = (s) => Buffer.from(s, 'hex');

function solve(html) {
    const nums = [...html.matchAll(/toNumbers\("([0-9a-f]+)"\)/g)].map((m) => m[1]);
    if (nums.length < 3) {
        return null;
    }
    const decipher = crypto.createDecipheriv('aes-128-cbc', hex(nums[0]), hex(nums[1]));
    decipher.setAutoPadding(false);

    return Buffer.concat([decipher.update(hex(nums[2])), decipher.final()]).toString('hex');
}

export async function hostFetch(url, { method = 'GET', timeoutMs = 120000 } = {}) {
    for (let attempt = 0; attempt < 3; attempt++) {
        const target = new URL(url);
        if (attempt > 0) {
            target.searchParams.set('i', String(attempt));
        }
        const res = await fetch(target, {
            method,
            redirect: 'follow',
            headers: { 'user-agent': UA, ...(cookie ? { cookie } : {}) },
            signal: AbortSignal.timeout(timeoutMs),
        });
        const text = await res.text();
        const answer = solve(text);
        if (answer === null) {
            return { status: res.status, text };
        }
        cookie = `__test=${answer}`;
    }
    throw new Error('the host kept asking for the browser check');
}

if (import.meta.url === `file://${process.argv[1].replace(/\\/g, '/')}` || process.argv[1]?.endsWith('hostfetch.mjs')) {
    const args = process.argv.slice(2);
    const url = args[0];
    const flag = (name) => (args.includes(name) ? args[args.indexOf(name) + 1] : null);
    const wantStatus = flag('--expect-status');
    const wantText = flag('--expect-text');
    try {
        const { status, text } = await hostFetch(url);
        console.log(`HTTP ${status}`);
        console.log(text);
        if ((wantStatus && String(status) !== wantStatus) || (wantText && !text.includes(wantText))) {
            console.error(`UNEXPECTED: wanted status ${wantStatus ?? 'any'} and text "${wantText ?? ''}"`);
            process.exit(1);
        }
    } catch (error) {
        console.error(`ERROR: ${error.message}`);
        process.exit(wantStatus === 'refused' ? 0 : 1);
    }
}
