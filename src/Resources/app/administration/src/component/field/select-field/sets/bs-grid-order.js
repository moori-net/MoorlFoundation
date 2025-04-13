export default() => {
  const options = [
    {value: 0, label: "auto"}
  ];

  for (let value = 1; value < 48; value++) {
    options.push({
      value,
      label: value
    });
  }

  return options;
}
