const density = 2;
const cycleTime = 2000;

const rand = (min, max) => min + Math.floor(Math.random() * max);

class Twinkle {
  static get inputProperties() {
    return [
      '--bg-color',
      '--dot-color',
      '--tick',
    ];
  }

  initializePointList(width, height) {
    this.pointList = [];
    const numPoints = width * height * density / 100000;
    for (let i = 0; i < numPoints; i++) {
      this.pointList.push({
        x: rand(0, width),
        y: rand(0, height),
        delay: rand(0, cycleTime),
      });
    }
  }

  paint(ctx, { width, height }, properties) {
    const bgColor = properties.get('--bg-color').toString();
    const dotColor = properties.get('--dot-color').toString();
    const tick = properties.get('--tick').toString();
    const size = 24;

    // Initialize point list
    if (this.pointList === undefined) {
      this.initializePointList(width, height);
    }

    // Draw background colour
    // ctx.fillStyle = bgColor;
    // ctx.fillRect(0, 0, width, height);

    // ctx.fillStyle = `rgba(0,0,0,${opacity})`;
    ctx.fillStyle = 'black';

    // for (const point of this.pointList) {
    //   ctx.fillRect(point.x, point.y, 2, 2);
    // }

    for (let i = 0; i < this.pointList.length; i++) {
      const point = this.pointList[i];
      ctx.fillRect(point.x, point.y, 2, 2);
    }
  }
}

registerPaint('twinkle', Twinkle);
