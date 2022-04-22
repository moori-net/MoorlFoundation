const rand = (min, max) => min + Math.floor(Math.random() * max);

class Dots {
  static get inputProperties() {
    return [
      '--bg-color',
      '--dot-color',
      '--mouse-x',
      '--mouse-y',
    ];
  }
  paint(ctx, { width, height }, properties) {
    const bgColor = properties.get('--bg-color').toString();
    const mouseX = properties.get('--mouse-x').toString();
    const mouseY = properties.get('--mouse-y').toString();
    const size = 24;

    // Draw background colour
    ctx.fillStyle = bgColor;
    ctx.fillRect(0, 0, width, height);

    // Draw in dots
    ctx.fillStyle = 'rgba(0,0,0,0.5)';
    for (let i = size / 2; i <= width; i += size) {
      for (let j = size / 2; j <= height; j += size) {
        let dotSize = 0.5;
        let opacity = 0.4;
        let green = 0;
        let blue = 0;

        if (mouseX && mouseY) {
          // Get distance from this point to the mouse
          const dist = Math.pow(mouseX - i, 2) + Math.pow(mouseY - j, 2);
          dotSize = Math.max(1, 2 - (dist / 10 / 1000));

          const colorfactor = (dist / 500) + 50;
          green = rand(colorfactor, colorfactor + 100);
          blue = rand(colorfactor, colorfactor + 100);
        }

        ctx.fillStyle = `rgba(0,${green},${blue},${opacity})`;
        ctx.beginPath();
        ctx.arc(i, j, dotSize, 0, Math.PI * 2);
        ctx.fill();
      }
    }

    // Draw in mouse position
    // if (mouseX && mouseY) {
    //   ctx.fillStyle = 'red';
    //   ctx.fillRect(mouseX, mouseY, 5, 5);
    // }
  }
}

registerPaint('dots', Dots);
