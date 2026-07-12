const PSD = require('psd');
const inputFile = process.argv[2];
const psd = PSD.fromFile(inputFile);
psd.parse();
process.stdout.write(psd.image.toPng());
