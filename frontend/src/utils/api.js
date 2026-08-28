const API_URL = 'http://api.pasteleria/';

export const api={
    //funcion para obtener data de la api
    get: async (endpoint) =>{
        try{
            const response = await fetch(`${API_URL}${endpoint}`);
            if(!response.ok){
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        }catch (error){
            console.error('Error al obtener data: ', error);
            throw error;
        }
    },
};