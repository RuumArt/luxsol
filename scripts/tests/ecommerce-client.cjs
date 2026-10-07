const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const script = fs.readFileSync(path.join(__dirname, '../../www/local/templates/luxol/build/js/ecommerce.js'), 'utf8');
const payload = {event: 'purchase', ecommerce: {transaction_id: 'paid-101', value: 18.45, currency: 'EUR', items: []}};
function page(initial = {}, unavailable = false) {
    const entries = new Map(Object.entries(initial));
    const window = {dataLayer: [], location: {}, sessionStorage: {
        getItem: key => entries.get(key) ?? null,
        setItem: (key, value) => { if (unavailable) throw Error('blocked'); entries.set(key, value); },
        removeItem: key => { if (unavailable) throw Error('blocked'); entries.delete(key); },
    }};
    const timers = [];
    vm.runInNewContext(script, {window, setTimeout: callback => timers.push(callback)});
    return {window, entries, timers};
}
const old = page({roomPendingPurchase: JSON.stringify(payload)});
assert.equal(old.window.dataLayer.length, 0, 'old unpaid purchase must not be replayed');
assert.equal(old.entries.has('roomPendingPurchase'), false);
const retry = page({roomPendingConfirmedPurchase: JSON.stringify(payload)});
assert.equal(retry.window.dataLayer[1].ecommerce.transaction_id, 'paid-101');
retry.window.dataLayer[1].eventCallback();
assert.equal(retry.entries.has('roomPendingConfirmedPurchase'), false);
const current = page();
current.window.roomSendPurchase(payload, '/next');
assert.equal(current.entries.has('roomPendingConfirmedPurchase'), true);
assert.equal(current.window.location.href, undefined);
current.timers[0]();
assert.equal(current.window.location.href, '/next');
assert.equal(current.entries.has('roomPendingConfirmedPurchase'), true, 'timeout keeps eligible purchase for retry');
current.window.dataLayer[1].eventCallback();
assert.equal(current.entries.has('roomPendingConfirmedPurchase'), false, 'late GTM acknowledgement clears retry');
const blocked = page({}, true);
blocked.window.roomSendPurchase(payload, '');
assert.equal(blocked.window.dataLayer[1].ecommerce.transaction_id, 'paid-101');
const corrupt = page({roomPendingConfirmedPurchase: '{invalid'});
assert.equal(corrupt.window.dataLayer.length, 0);
console.log('Client ecommerce checks passed: old unpaid queue, retries, timeout, blocked storage and corrupted data.');
