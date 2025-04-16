const options = [
    { value: -1, label: 'auto-fill' },
    { value: 0, label: 'auto-fit' },
];

for (let value = 1; value <= 12; value++) {
    options.push({
        value,
        label: `${value}/12`,
    });
}

export default options;
