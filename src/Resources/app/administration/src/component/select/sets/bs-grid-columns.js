export default() => {
  const options = [];

  for (let value = 1; value < 12; value++) {
    options.push({
      value,
      label: value
    });
  }

  return options;
}
