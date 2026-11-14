const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

function render(point) {
    let config;
    const context = vm.createContext({
        Chart: class {
            constructor(_canvas, options) { config = options; }
            resize() {}
        },
        document: { addEventListener() {} },
        window: { matchMedia: () => ({ matches: false }) },
    });
    const source = fs.readFileSync(path.join(__dirname, '../../resources/js/plotopcharts.js'), 'utf8');
    vm.runInContext(source.replace("import Chart from 'chart.js/auto';", ''), context);
    context.canvas = { closest: () => null };
    context.dataset = { datasets: [{ data: [{ C: 'g1', H: '1', OP: -0.2, ...point }] }] };
    vm.runInContext("drawOneChart(canvas, dataset, ['Simulation'], 'OP')", context);
    return config;
}

for (const [name, point, error] of [
    ['new error-bar field', { ERRBAR: 0.01 }, 0.01],
    ['legacy stored field', { STD: 0.02 }, 0.02],
    ['new field takes priority', { ERRBAR: 0.01, STD: 0.2 }, 0.01],
    ['zero error', { ERRBAR: 0 }, 0],
    ['missing error', {}, null],
    ['explicit missing error', { ERRBAR: null, STD: 0.2 }, null],
]) {
    test(name, () => {
        const config = render(point);
        const dataPoint = config.data.datasets[0].data[0];
        const label = config.options.plugins.tooltip.callbacks.label({ raw: dataPoint });
        assert.equal(label, error === null ? 'OP: -0.200' : `OP: -0.200 ± ${error.toFixed(3)}`);
        const segments = [];
        const chart = {
            data: config.data,
            getDatasetMeta: () => ({ data: [{ x: 10 }] }),
            scales: { y: { getPixelForValue: value => value } },
            ctx: {
                save() {}, restore() {}, beginPath() {}, stroke() {},
                moveTo: (x, y) => segments.push([x, y]),
                lineTo: (x, y) => segments.push([x, y]),
            },
        };
        config.plugins[0].afterDataLimits(chart, {}, { enabled: true });
        const expectedError = error ?? 0;
        const padding = expectedError === 0 ? 0.05 : 0.02;
        assert.equal(chart.scales.y.min, -0.2 - expectedError - padding);
        assert.equal(chart.scales.y.max, -0.2 + expectedError + padding);
        config.plugins[0].afterDatasetsDraw(chart, {}, { enabled: true });
        assert.equal(segments.length, error === null ? 0 : 6);
        if (error !== null) {
            assert.equal(segments[0][1], -0.2 + error);
            assert.equal(segments[1][1], -0.2 - error);
        }
    });
}
