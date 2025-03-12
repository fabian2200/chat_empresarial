import axios from 'axios';
import { baseUrl } from '../baseUrl';

export function http() {
    return axios.create({
        baseURL: 'https://ingeer.co'+baseUrl+'api/'
        //baseURL: 'http://192.168.1.17'+baseUrl+'api/'
    });
}