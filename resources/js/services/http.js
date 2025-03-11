import axios from 'axios';
import { baseUrl } from '../baseUrl';

export function http() {
    return axios.create({
        baseURL: 'https://rxssgswx.lucusvirtual.es'+baseUrl+'api/'
        //baseURL: 'http://127.0.0.1:8000/api/'
        //baseURL: 'https://192.168.0.45'+baseUrl+'api/'
    });
}