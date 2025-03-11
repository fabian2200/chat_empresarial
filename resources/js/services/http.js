import axios from 'axios';
import { baseUrl } from '../baseUrl';

export function http() {
    return axios.create({
        //baseURL: 'https://rxssgswx.lucusvirtual.es'+baseUrl+'api/'
        baseURL: 'http://192.168.1.14'+baseUrl+'api/'
    });
}