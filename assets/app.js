import './bootstrap';
import { startStimulusApp } from '@symfony/stimulus-bridge';
import '@symfony/autoimport';


export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));


import './styles/app.css';


import 'chart.js';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');


