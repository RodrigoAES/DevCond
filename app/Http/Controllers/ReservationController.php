<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Area;
use App\Models\AreaDisabledDay;
use App\Models\AreaReservation;
use App\Models\Unit;
use App\Models\UnitResident;

class ReservationController extends Controller
{
    public function getAll() {
        $response = ['error' => false];

        $areas = Area::where('allowed', true)->get();

        $daysHandler = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        foreach($areas as $area) {
            $area->days = explode(',', $area->days);
            $days = [];
            $count = 0;
            foreach($area->days as $index => $day) {
                if($index > 0) {
                    if($day == $area->days[$index-1] + 1) {
                        $days[$count][] = $day;
                    } else {
                        $count++;
                        $days[$count][] = $day;
                    }
                } else {
                    $days[$count][] = $day;
                }
            }
            $weekDays = [];
            foreach($days as $dayGroup) {
               $weekDays[] = $daysHandler[array_shift($dayGroup)].'-'.$daysHandler[array_pop($dayGroup)].
               ' das '.date('H:i',strtotime($area->start_time)).' às '.date('H:i',strtotime($area->end_time));
            }
            $area->days = $weekDays;
            $area->cover = url("storage/images/$area->cover"); 

            unset($area->allowed);
            unset($area->start_time);
            unset($area->end_time);
        }
        
        $response['areas'] = $areas;
        return response()->json($response, 200);
    }

    public function getDisabledDays($id) {
        $response = ['error' => false];

        $daysHandler = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        $area = Area::where('id', $id)->first();
        if($area) {
            for($k=0; $k<7; $k++) {
                if(! in_array($k, explode(',', $area->days))){
                    $disableds[] = $k;
                }
            }  
            foreach($area->disabledDays as $day) {
                $eventualDisabledDays[] = $day->day;
            } 
            
            for($k=0; $k<90; $k++) {
                $timestamp = $k > 0 ? strtotime("+$k days") : time();
                if(in_array(date('w', $timestamp), $disableds)){
                    $disabledDates[] = date('d/m/Y', $timestamp).' - '.$daysHandler[date('w', $timestamp)];

                } elseif(in_array(date('Y-m-d',  $timestamp), $eventualDisabledDays)) {
                    $disabledDates[] = date('d/m/Y', $timestamp).' - '.$daysHandler[date('w', $timestamp)];
                }
                
            }
            $response['disabledDates'] = $disabledDates;
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Area não existente.';
            return response()->json($response, 404);
        }
    }

    public function getTimes($id, Request $request) {
        $response = ['error' => false];
        
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|date_format:Y-m-d'
        ], [
            'required' => 'É necessario o envio do campo data.',
            'date' => 'Data inválida.',
            'date_format' => 'Data de formato inválido (Ano-mês-dia)'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $date = $validator->validated()['date'];
        $area = Area::find($id);

        if($area) {
            $disabled = AreaDisabledDay::where('area_id', $area->id)
                ->where('day', $date)
            ->first();
            if(in_array(date('w', strtotime($date)), explode(',', $area->days))) {
                if(! $disabled) {
                    $areaOpStart = strtotime($area->start_time);
                    $areaOpEnd = strtotime($area->end_time);

                    for(
                        $time = $areaOpStart;
                        $time < $areaOpEnd;
                        $time = strtotime('+1 hour', $time)
                    ) {
                        $operatingTimes[] = date('H:i:s', $time);
                    }
                    
                    $reservations = AreaReservation::whereDate('reservation_datetime', $date)->get();
                    if(count($reservations) > 0) {
                        foreach($reservations as $key => $reservation) {
                            $indexStart = array_search(explode(' ',$reservation->reservation_datetime)[1], $operatingTimes);
                            $indexEnd = array_search(explode(' ',$reservation->reservation_endtime)[1], $operatingTimes);
                            $diference = $indexEnd - $indexStart;

                            array_splice($operatingTimes, $indexStart, $diference);
                        }
                    }
                    $avaliableTimes = $operatingTimes;
                    foreach($avaliableTimes as $key => $time) {
                        $timestamp = strtotime($time); 

                        $avaliableTimes[$key] = [];
                        $avaliableTimes[$key]['hour'] = date('H:i', $timestamp); 
                        $avaliableTimes[$key]['text'] = date('H:i', $timestamp).' às '.date('H:i',  strtotime('+1 hour', $timestamp));
                    }

                    $response['times'] = $avaliableTimes;
                    return response()->json($response, 200);
    
                } else {
                    $ree['error'] = "Nesta data a $area->title não funcionará.";
                    return response()->json($response);
                }
            } else {
                $response['error'] = "Neste dia da semana a $area->title não funciona.";
                return response()->json($response);
            }   
        } else {
            $response['error'] = 'Area inexistente.';
            return response()->json($response, 404);
        }
    }

    public function setReservation($id, Request $request) {
        $response = ['error' => false];

        $today = date('Y-m-d');
        $validator = Validator::make($request->all(), [
            'date' => "required|date|date_format:Y-m-d|after_or_equal:$today",
            'start_time' => "required|date_format:H:i:s",
            'end_time' => 'required|date_format:H:i:s',
            'unit_id' => 'required|numeric'
        ], [
            'required' => 'Campo obrigatório.',
            'date.date' => 'O campo deve conter uma data válida.',
            'date.date_format' => 'A data deve conter um formato válido (Ano-mês-dia).',
            'date.after_or_equal' => 'Data inválida.',
            'start_time.date_format' => 'Formato de hora inválido (Hora:minuto:segundo).',
            'end_time.date_format' => 'Formato de hora inválido (Hora:minuto:segundo).',
            'unit_id.numeric' => 'ID inválido.'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
        }

        $date = $validator->validated()['date'];
        $start_time = $validator->validated()['start_time'];
        $end_time = $validator->validated()['end_time'];
        $property = $validator->validated()['unit_id'];

        $now = date('H:i:s');
        if($date === $today && $start_time < $now) {
            $response['error'] = ['start_time' => 'Hora inválida'];
            return response()->json($response, 422);
        }

        $area = Area::find($id);
        $unit = Unit::find($property);
        if($unit) {
            if($area) {
                $authorization = UnitResident::where('user_id', Auth::id())
                    ->where('unit_id', $property)
                    ->first() ??
                Unit::where('user_id', Auth::id())
                    ->where('id', $property)
                    ->first();

                if($authorization) {
                    if(in_array(date('w', strtotime($date)), explode(',', $area->days))) {
                        $disabled = AreaDisabledDay::whereDate('area_id', $id)
                        ->whereDate('day', $date)
                        ->count();
        
                        if($disabled === 0) {
                            for(
                                $time = strtotime($area->start_time);
                                $time < strtotime($area->end_time);
                                $time = strtotime('+1 hour', $time)
                            ) {
                                $areaHours[] = date('H:i:s', $time);
                            }
                            $reserveds = AreaReservation::whereDate('reservation_datetime',$date)->get();
                            if(count($reserveds) > 0) {
                                foreach($reserveds as $reserved) {
                                    $start = array_search(explode(' ',$reserved->reservation_datetime)[1], $areaHours);
                                    $end = array_search(explode(' ',$reserved->reservation_endtime)[1], $areaHours);
                                    $diference = $end - $start;
        
                                    array_splice($areaHours, $start, $diference);
                                }   
                            }
                            for(
                                $time = strtotime($start_time);
                                $time < strtotime($end_time);
                                $time = strtotime('+1 hour', $time)
                            ) {
                                $requisitedHours[] = date('H:i:s', $time);
                            }
                            $avaliable = true;
                            foreach($requisitedHours as $hour) {
                                if(! in_array($hour, $areaHours)) {
                                    $avaliable = false;
                                }
                            }
        
                            if($avaliable){
                                $response['reservation'] = AreaReservation::create([
                                    'unit_id' => $property,
                                    'area_id' => $id,
                                    'reservation_datetime' => "$date $start_time",
                                    'reservation_endtime' => "$date $end_time"
                                ]);
                                if($response['reservation']) {
                                    return response()->json($response, 200);
        
        
                                } else {
                                    $response['error'] = 'Erro interno ao criar registro. Por favor tente novamente.';
                                    return response()->json($response, 500);
                                }
                            } else {
                                $response['error'] = 'Horario não disponivel para reserva.';
                                return response()->json($response, 422);
                            }
                        } else {
                            $response['error'] = 'Area não funcionará na data requisitada.';
                            return response()->json($response, 422);
                        }
                    } else {
                        $response['error'] = 'Area não funciona na data requisitada.';
                        return response()->json($response, 422);
                    }
                } else {
                    $response['error'] = 'Acesso não autorizado.';
                    return response()->json($response, 401);
                }
            } else {
                $response['error'] = 'Area inexistente.';
                return response()->json($response, 404);
            }
        } else {
            $response['error'] = 'Propriedade inexistente.';
            return response()->json($response, 404);
        }
    }

    public function userReservations(Request $request) {
        $response = ['error' => false];

        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|numeric'
        ], [
            'required' => 'ID da propriedade é necessario.',
            'numeric' => 'Formato de ID invalido.'
        ]);
        if($validator->fails()) {
            $response['error'] = $validator->errors();
            return response()->json($response, 422);
            exit;
        }

        $property = $validator->validated()['unit_id'];

        $authorization = UnitResident::where('user_id', Auth::id())
            ->where('unit_id', $property)
            ->first() ?? 
        Unit::where('user_id', Auth::id())
            ->where('id', $property)
            ->first();
        if($authorization) {
            $property = $authorization->unit ?? $authorization;

            $reservations = AreaReservation::where('unit_id', $property->id)
                ->orderBy('reservation_datetime', 'DESC')
            ->get();

            foreach($reservations as $reservation) {
                $reservation->datetime = 
                    date('d/m/Y H:i', strtotime($reservation->reservation_datetime)).' às '.
                    date('H:i', strtotime($reservation->reservation_endtime))
                ;

                $reservation->area->cover = url("storage/images/".$reservation->area->cover);

                unset($reservation->reservation_datetime);
                unset($reservation->reservation_endtime);
                unset($reservation->area_id);
                unset($reservation->area->allowed);
                unset($reservation->area->days);
                unset($reservation->area->start_time);
                unset($reservation->area->end_time);
            }

            $response['reservations'] = $reservations;
            return response()->json($response, 200);

        } else {
            $response['error'] = 'Acesso não authorizado.';
            return response()->json($response, 401);
        }
    }

    public function removeReservation($id) {
        $response = ['error' => false];

        $reservation = AreaReservation::find($id);
        if($reservation) {
            $authorization = Unit::where('user_id', Auth::id())
                ->where('id', $reservation->unit->id)
            ->first();

            if($authorization) {
                $response['deleted'] = $reservation->delete();
                if($response['deleted']) {
                    return response()->json($response, 200);

                } else {
                    $response['error'] = 'Erro interno ao fazer ação. Por favor tente novamente.';
                    return response()->json($response, 500);
                }
            } else {
                $response['error'] = 'Acesso não autorizado.';
                return response()->json($response, 401);
            }
        } else {
            $response['error'] = 'Reserva inexistente.';
            return response()->json($response, 404);
        }


    }
    
}
